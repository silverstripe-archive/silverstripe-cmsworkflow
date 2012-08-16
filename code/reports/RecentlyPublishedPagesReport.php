<?php

/**
 * Show all publishing activity across the site
 *
 * @package cmsworkflow
 * @subpackage reports
 */
class RecentlyPublishedPagesReport extends SS_Report {
	function title() {
		return _t('RecentlyPublishedPagesReport.TITLE', 'Recently published pages');
	}
	function description() {
		return _t('RecentlyPublishedPagesReport.DESCRIPTION', 'Note that this report only lists pages that still appear on the published site.');
	}
	
	function columns() {
		$fields = array(
			"PageTitle" => array(
				"title" => "Page name",
				'formatting' => '<a href=\"admin/show/$ID\" title=\"Edit page\">$value</a>'
			),
			'Published' => array(
				'title' => 'Published',
				'casting' => 'SS_Datetime->Full'
			),
			'PublisherTitle' => 'Publisher',
			'AbsoluteLink' => array(
				'title' => 'URL',
				'formatting' => '$value <br />" . ($AbsoluteLiveLink ? "<a target=\"_blank\" href=\"$AbsoluteLiveLink\">(live)</a>" : "") . " " . (!$IsDeletedFromStage ? "<a target=\"_blank\" href=\"$AbsoluteLink\">(draft)</a>" : "") . "'
			),
			'DiffLink' => array(
				'title' => 'Changes',
				'formatting' => '<a target=\"_blank\" href=\"$DiffLink\">' . _t('PublishedPages.ShowChanges', 'Show changes') . '</a>'
			),
			'ExpiryDate' => array(
				'title' => 'Expiry',
				'formatting' => '" . ($value && $value != "0000-00-00 00:00:00" ? date("j M Y g:ia", strtotime($value)) : "no") . "',
				'csvFormatting' => '" . ($value && $value != "0000-00-00 00:00:00" ? date("j M Y g:ia", strtotime($value)) : "no") . "'
			)
		);
		return $fields;
	}
	
	function sourceRecords($params, $sort, $limit) {
		increase_time_limit_to(120);
		
		// Emulate Form->loadDataFrom()
		$fields = $this->parameterFields();
		foreach($fields as $field) {
			if(isset($params[$field->Name()])) {
				$val = $params[$field->Name()];
				if($val) $field->setValue($val);
			}
		}

		$summarize = (isset($params['SummarizeChanges']) && $params['SummarizeChanges']);

		$origStage = Versioned::current_stage();
		Versioned::reading_stage('Live');
		$join = implode(' ', array(
			'LEFT JOIN "WorkflowRequest" ON "WorkflowRequest"."PageID" = "SiteTree_Live"."ID"',
			'LEFT JOIN "Member" ON "WorkflowRequest"."PublisherID" = "Member"."ID"'
		));
		$q = singleton('SiteTree')->extendedSQL(null, null, null, $join);
		Versioned::reading_stage($origStage);
		$q->select[] = '"SiteTree_Live"."Title" AS "PageTitle"';
		$q->select[] = "\"WorkflowRequest\".\"LastEdited\" AS \"Published\"";
		$q->groupby[] = "\"WorkflowRequest\".\"LastEdited\"";
		$q->where[] = "\"WorkflowRequest\".\"ClassName\" = 'WorkflowPublicationRequest'";
		$q->where[] = "\"WorkflowRequest\".\"Status\" = 'Completed'";
		$q->select[] = 'COUNT("WorkflowRequest"."Status") AS "WorkflowRequestCount"';
		$q->select[] = Member::get_title_sql().' AS "PublisherTitle"';
		$q->groupby[] = '"Member"."FirstName"';
		$q->groupby[] = '"Member"."Surname"';
		
		// restrict to member id
		if (!empty($params['memberId']) && DataObject::get_by_id('Member', $params['memberId'])) {
			$q->where[] = "\"Member\".\"ID\" = ".Convert::raw2sql($params['memberId']);
			$q->groupby[] = '"Member"."ID"';
		}
		
		// Date calculations
		$startDate = !empty($params['StartDate']) ? $params['StartDate'] : null;
		$endDate = !empty($params['EndDate']) ? $params['EndDate'] : null;
		if($startDate) $startDate = $fields->dataFieldByName('StartDate')->dataValue();
		if($endDate) $endDate = $fields->dataFieldByName('EndDate')->dataValue();
		if ($startDate && $endDate) {
			$q->where[] = "\"WorkflowRequest\".\"LastEdited\" >= '".Convert::raw2sql($startDate)."' AND \"WorkflowRequest\".\"LastEdited\" <= '".Convert::raw2sql($endDate)."'";
		} else if ($startDate && !$endDate) {
			$q->where[] = "\"WorkflowRequest\".\"LastEdited\" >= '".Convert::raw2sql($startDate)."'";
		} else if (!$startDate && $endDate) {
			$q->where[] = "\"WorkflowRequest\".\"LastEdited\" <= '".Convert::raw2sql($endDate)."'";
		} else {
			$q->where[] = "\"WorkflowRequest\".\"LastEdited\" >= '".SS_Datetime::now()->URLDate()."'";
		}
		
		// Turn a query into records
		if($sort) {
			$parts = explode(' ', $sort);
			$field = $parts[0];
			$direction = $parts[1];
			
			if($field == 'AbsoluteLink') {
				$sort = '"URLSegment" ' . $direction;
			} elseif($field == '"Subsite"."Title"') {
				$q->from[] = 'LEFT JOIN "Subsite" ON "Subsite"."ID" = "SiteTree_Live"."SubsiteID"';
			}

			$q->orderby = $sort;
		}

		// Avoid duplicating records (=change entries) if summarizing the changes
		if($summarize) array_unshift($q->groupby, '"SiteTree"."ID"');

		// Fetch all records (unlimited)
		$records = singleton('SiteTree')->buildDataObjectSet($q->execute(), 'DataObjectSet', $q);

		// Apply limit after that filtering
		if($limit && $records) $records = $records->getRange($limit['start'], $limit['limit']);

		if($records) foreach($records as $record) {
			if($startDate) {
				$fromVersionRecords = $record->allVersions(
					sprintf('"SiteTree_versions"."LastEdited" < \'%s\'', Convert::raw2sql($startDate)),
					'"SiteTree_versions"."Version" DESC',
					1
				);
				$fromVersion = $fromVersionRecords->Count() ? $fromVersionRecords->First()->Version : 1;
			} else if($summarize) {
				$fromVersion = ($record->Version > 1) ? $record->Version - 1 : 1;
			} else {
				$fromVersion = 1;
			} 

			if($endDate && $summarize) {
				$toVersionRecords = $record->allVersions(
					sprintf('"SiteTree_versions"."LastEdited" < \'%s\'', Convert::raw2sql($endDate)),
					'"SiteTree_versions"."Version" DESC',
					1
				);
				$toVersion = $toVersionRecords->Count() ? $toVersionRecords->First()->Version : $record->Version;
			} else if($summarize) {
				$toVersion = $record->Version;
			} else {
				$toVersion = $record->Version;
			} 
			
			$record->DiffLink = sprintf(
				'%s/%d/?From=%d&To=%d',
				singleton('CMSMain')->Link('compareversions'),
				$record->ID,
				// Note changed parameter order, naming doesn't match logic in core
				$toVersion,
				$fromVersion
			);
		}

		return $records;
	}
	
	function parameterFields() {
		$params = new FieldSet();

		$options = DataObject::get('Member')->toDropdownMap('ID', 'Name', 'Any');
		$params->push(new DropdownField(
			"memberId", 
			"Member", 
			$options
		));
		
		$params->push($startDate = Object::create('DatetimeField', 'StartDate', 'Start date'));
		$params->push($endDate = Object::create('DatetimeField', 'EndDate', 'End date'));
		$startDate->getDateField()->setConfig('showcalendar', true);
		$startDate->getTimeField()->setValue('23:59:59');
		$endDate->getDateField()->setConfig('showcalendar', true);
		$endDate->getTimeField()->setValue('23:59:59');

		$params->push(new CheckboxField(
			'SummarizeChanges', _t('RecentlyPublishedPagesReport.Summarize', 'Summarize changes?')
		));
		
		return $params;
	}
}
