<?php
/**
 * Report to show pages scheduled to be published
 *
 * @package cmsworkflow
 * @subpackage reports
 */
class PagesScheduledForPublishingReport extends SSReport {
	function title() {
		return _t('PagesScheduledForPublishingReport.TITLE',"Approved pages with Embargo on");
	}
		
	function sourceRecords($params, $sort, $limit) {
		$wheres = array();
		
		$startDate = !empty($params['StartDate']) ? $params['StartDate'] : null;
		$endDate = !empty($params['EndDate']) ? $params['EndDate'] : null;
		
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
			$wheres[] = "EmbargoDate >= '".Convert::raw2sql($startDate)."' AND EmbargoDate <= '".Convert::raw2sql($endDate)."'";
		} else if ($startDate && !$endDate) {
			$wheres[] = "EmbargoDate >= '".Convert::raw2sql($startDate)."'";
		} else if (!$startDate && $endDate) {
			$wheres[] = "EmbargoDate <= '".Convert::raw2sql($endDate)."'";
		} else {
			$wheres[] = "EmbargoDate >= '".SSDatetime::now()->URLDate()."'";
		}
		
		$wheres[] = "WorkflowRequest.Status = 'Scheduled'";
		
		$query = singleton("SiteTree")->extendedSQL(join(' AND ', $wheres), null, null, 
			"LEFT JOIN WorkflowRequest on WorkflowRequest.PageID = SiteTree.ID"
		);
		
		$query->select[] = "WorkflowRequest.EmbargoDate AS EmbargoDate";
		
		$query->from[] = "LEFT JOIN Member AS Approver ON WorkflowRequest.ApproverID = Approver.ID";
		$query->select[] = Member::get_title_sql('Approver').' AS ApproverName';

		// Turn a query into records
		if($sort) $query->orderby = $sort;
		$records = singleton('SiteTree')->buildDataObjectSet($query->execute(), 'DataObjectSet', $query);

		// Filter to only those with canEdit permission
		$filteredRecords = new DataObjectSet();
		if($records) foreach($records as $record) {
			if($record->canEdit()) $filteredRecords->push($record);
		}
		
		// Apply limit after that filtering.
		if($limit) return $filteredRecords->getRange($limit['start'], $limit['limit']);
		else return $filteredRecords;
	}

	function columns() {
		$fields = array(
			"Title" => array(
				"title" => "Title",
				'formatting' => '<a href=\"admin/show/$ID\" title=\"Edit page\">$value</a>'
			),
			'EmbargoDate' => array(
				'title' => 'Will be published at',
				'casting' => 'SSDatetime->Full'
			),
			'ApproverName' => 'Approved by',
			'AbsoluteLink' => array(
				'title' => 'URL',
				'formatting' => '$value " . ($AbsoluteLiveLink ? "<a href=\"$AbsoluteLiveLink\">(live)</a>" : "") . " <a href=\"$value?stage=Stage\">(draft)</a>'
			)
		);
		
		return $fields;
	}
	
	function parameterFields() {
		$params = new FieldSet();
		
		$params->push($startDate = new PopupDateTimeField('StartDate', 'Start date'));
		$params->push($endDate = new PopupDateTimeField('EndDate', 'End date'));
		$endDate->defaultToEndOfDay();
		$startDate->allowOnlyTime(false);
		$endDate->allowOnlyTime(false);

		return $params;
	}
}
