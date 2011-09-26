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
				'formatting' => '$value " . ($AbsoluteLiveLink ? "<a target=\"_blank\" href=\"$AbsoluteLiveLink\">(live)</a>" : "") . " " . (!$IsDeletedFromStage ? "<a target=\"_blank\" href=\"$AbsoluteLink\">(draft)</a>" : "") . "'
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
		
		$origStage = Versioned::current_stage();
		Versioned::reading_stage('Live');
		$q = singleton('SiteTree')->extendedSQL();
		Versioned::reading_stage($origStage);
		$q->select[] = '"SiteTree_Live"."Title" AS "PageTitle"';
	
		$q->leftJoin('WorkflowRequest', '"WorkflowRequest"."PageID" = "SiteTree_Live"."ID"');
		$q->select[] = "\"WorkflowRequest\".\"LastEdited\" AS \"Published\"";
		$q->where[] = "\"WorkflowRequest\".\"ClassName\" = 'WorkflowPublicationRequest'";
		$q->where[] = "\"WorkflowRequest\".\"Status\" = 'Completed'";
		
		
		$q->leftJoin('Member', '"WorkflowRequest"."PublisherID" = "Member"."ID"');
		$q->select[] = Member::get_title_sql().' AS "PublisherTitle"';
		
		// restrict to member id
		if (!empty($params['memberId']) && DataObject::get_by_id('Member', $params['memberId'])) {
			$q->where[] = "\"Member\".\"ID\" = ".Convert::raw2sql($params['memberId']);
		}
		
		$startDate = isset($params['StartDate']) ? $params['StartDate'] : null;
		$endDate = isset($params['EndDate']) ? $params['EndDate'] : null;
		
		if($startDate) {
			if(count(explode('/', $startDate['Date'])) == 3) {
				list($d, $m, $y) = explode('/', $startDate['Date']);
				$startDate['Time'] = $startDate['Time'] ? $startDate['Time'] : '00:00:00';
				$startDate = @date('Y-m-d H:i:s', strtotime("$y-$m-$d {$startDate['Time']}"));
			} else {
				$startDate = null;
			}
		}
		
		if($endDate) {
			if(count(explode('/', $endDate['Date'])) == 3) {
				list($d,$m,$y) = explode('/', $endDate['Date']);
				$endDate['Time'] = $endDate['Time'] ? $endDate['Time'] : '23:59:59';
				$endDate = @date('Y-m-d H:i:s', strtotime("$y-$m-$d {$endDate['Time']}"));
			} else {
				$endDate = null;
			}
		}
		
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
		$records = singleton('SiteTree')->buildDataObjectSet($q->execute(), 'DataObjectSet', $q);

		// Apply limit after that filtering.
		if($limit && $records) return $records->getRange($limit['start'], $limit['limit']);
		else return $records;
	}
	
	function parameterFields() {
		$params = new FieldSet();

		$options = DataObject::get('Member')->toDropdownMap('ID', 'Name', 'Any');
		$params->push(new DropdownField(
			"memberId", 
			"Member", 
			$options
		));
		
		if(class_exists('PopupDateTimeField')) {
			$params->push($startDate = new PopupDateTimeField('StartDate', 'Start date'));
			$params->push($endDate = new PopupDateTimeField('EndDate', 'End date'));
			$endDate->defaultToEndOfDay();
			$startDate->allowOnlyTime(false);
			$endDate->allowOnlyTime(false);
			$endDate->mustBeAfter($startDate->Name());
			$startDate->mustBeBefore($endDate->Name());
		} else {
			$params->push($startDate = new DateTimeField('StartDate', 'Start date'));
			$params->push($endDate = new DateTimeField('EndDate', 'End date'));
		}
		
		return $params;
	}
}
