<?php
/**
 * Report to show pages scheduled to be deleted
 *
 * @package cmsworkflow
 * @subpackage reports
 */
class PagesScheduledForDeletionReport extends SSReport {
	function title() {
		return _t('PagesScheduledForDeletionReport.TITLE',"Published pages with Expiry on");
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
	
	function columns() {
		$fields = array(
			"Title" => array(
				"title" => "Page name",
				'formatting' => '<a href=\"admin/show/$ID\" title=\"Edit page\">$value</a>'
			),
			'ExpiryDate' => array(
				'title' => 'Will be deleted at',
				'casting' => 'SSDatetime->Full'
			),
			'ApproverName' => 'Approved by',
			'AbsoluteLink' => array(
				'title' => 'URL',
				'formatting' => '$value " . ($AbsoluteLiveLink ? "<a href=\"$AbsoluteLiveLink\">(live)</a>" : "") . " <a href=\"$value?stage=Stage\">(draft)</a>'
			),
			"BacklinkCount" => array(
				"title" => "Incoming links",
				'formatting' => '".($value ? "<a href=\"admin/show/$ID#Root_Expiry\" title=\"View backlinks\">yes, $value</a>" : "none") . "'
			),
		);
		
		return $fields;
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
		
		if($startDate && $endDate) {
			$wheres[] = "ExpiryDate >= '".Convert::raw2sql($startDate)."' AND ExpiryDate <= '".Convert::raw2sql($endDate)."'";
		} else if($startDate && !$endDate) {
			$wheres[] = "ExpiryDate >= '".Convert::raw2sql($startDate)."'";
		} else if(!$startDate && $endDate) {
			$wheres[] = "ExpiryDate <= '".Convert::raw2sql($endDate)."'";
		} else {
			$wheres[] = "ExpiryDate >= '".SSDatetime::now()->URLDate()."'";
		}
		
		$stage = Versioned::current_stage();
		Versioned::reading_stage('Live');
		
		$query = singleton("SiteTree")->extendedSQL(join(' AND ', $wheres), null, null, 
			"LEFT JOIN WorkflowRequest on WorkflowRequest.PageID = SiteTree_Live.ID"
		);

		
		$query->from[] = "LEFT JOIN Member AS Approver ON WorkflowRequest.ApproverID = Approver.ID";
		$query->select[] = Member::get_title_sql('Approver').' AS ApproverName';
			
		
		// Turn a query into records
		if($sort) $query->orderby = $sort;
		$records = singleton('SiteTree')->buildDataObjectSet($query->execute(), 'DataObjectSet', $query);

		Versioned::reading_stage($stage);
		
		// Filter to only those with canEdit permission
		$filteredRecords = new DataObjectSet();
		if($records) foreach($records as $record) {
			$record->BacklinkCount = $record->BackLinkTracking()->Count();
			if($record->canEdit()) $filteredRecords->push($record);
		}
		
		// Apply limit after that filtering.
		if($limit) return $filteredRecords->getRange($limit['start'], $limit['limit']);
		else return $filteredRecords;
	}
}
	
