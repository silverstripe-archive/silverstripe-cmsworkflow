<?php
/**
 * Report to show pages scheduled to be published
 *
 * @package cmsworkflow
 * @subpackage reports
 */
class PagesScheduledForPublishingReport extends SSReport {
	function TreeTitle() {
		return _t('PagesScheduledForPublishingReport.TITLE',"Pages scheduled for publishing");
	}
	function getReportField() {
		// Manually manage the subsite filtering
		if (ClassInfo::exists('Subsite')) Subsite::$disable_subsite_filter = true;
		
		$wheres = array();
		
		$sessionData = Session::get('ReportParams_'.__CLASS__);
		$reportData = array_merge(is_array($sessionData)?$sessionData:array(), $_REQUEST);
		Session::set('ReportParams_'.__CLASS__, $reportData);
		
		if (isset($reportData['Subsites'])) {
			// 'any' wasn't selected
			$subsiteIds = array();
			foreach($reportData['Subsites'] as $subsite) {
				if (is_numeric($subsite)) $subsiteIds[] = $subsite;
			}
			$wheres[] = "SubsiteID IN(".join(',', $subsiteIds).")";
		}
		
		$startDate = isset($reportData['StartDate']) ? $reportData['StartDate'] : null;
		$endDate = isset($reportData['EndDate']) ? $reportData['EndDate'] : null;
		
		if ($startDate) {
			if (count(explode('/', $startDate['Date'])) == 3) {
				list($d,$m,$y) = explode('/', $startDate['Date']);
				$startDate['Time'] = $startDate['Time'] ? $startDate['Time'] : '00:00:00';
				$startDate = @date('Y-m-d H:i:s', strtotime("$y-$m-$d {$startDate['Time']}"));
			} else { $startDate = null; }
		}
		if ($endDate) {
			if (count(explode('/', $endDate['Date'])) == 3) {
				list($d,$m,$y) = explode('/', $endDate['Date']);
				$endDate['Time'] = $endDate['Time'] ? $endDate['Time'] : '23:59:59';
				$endDate = @date('Y-m-d H:i:s', strtotime("$y-$m-$d {$endDate['Time']}"));
			} else { $endDate = null; }
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
		
		$wheres[] = '"WorkflowRequest"."Status" = \'Scheduled\'';

		// $res = DataObject::get('SiteTree', ' AND '.$where, null, "LEFT JOIN WorkflowRequest on WorkflowRequest.PageID = SiteTree.ID");
		// 
		// $doSet = new DataObjectSet();
		// if (!$res) return false;
		// foreach ($res as $page) {
		// 	$page->WFTimeOfPublishing = $page->openWorkflowRequest()->EmbargoDate;
		// 	$doSet->push($page);
		// }
			
		if (ClassInfo::exists('Subsite')) {
			$tlf = new TableListField('ReportItems', 'SiteTree', array(
				'Title' => 'Title',
				'Subsite.Title' => 'Subsite',
				'openWorkflowRequest.EmbargoDate' => 'Will be published at',
				'openWorkflowRequest.Approver.Title' => 'Approved by',
				'ID' => 'Actions',
				'AbsoluteLink' => 'Links',
			), join(' AND ', $wheres), "ExpiryDate DESC",
				"LEFT JOIN WorkflowRequest on WorkflowRequest.PageID = SiteTree.ID");
		} else {
			$tlf = new TableListField('ReportItems', 'SiteTree', array(
				'Title' => 'Title',
				'openWorkflowRequest.EmbargoDate' => 'Will be published at',
				'ID' => 'Actions',
				'AbsoluteLink' => 'Links',
			), join(' AND', $wheres), "ExpiryDate DESC",
				"LEFT JOIN WorkflowRequest on WorkflowRequest.PageID = SiteTree.ID");
		}
	
		$tlf->setFieldFormatting(array(
			'ID' => '<a href=\"admin/show/$value\">Edit in CMS</a>',
			'AbsoluteLink' => '$value <a href=\"$value?stage=Live\">(live)</a> <a href=\"$value?stage=Stage\">(draft)</a>',
		));
		
		$tlf->setFieldCasting(array(
			'openWorkflowRequest.EmbargoDate' => 'SSDatetime->Nice'
		));
		
		$tlf->actions = array();
		
		return $tlf;
	}
	
	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->insertBefore($params = new Tab('Search Parameters'), 'Report');
		
		if (class_exists('Subsite') && $subsites = DataObject::get('Subsite')) {
			$options = $subsites->toDropdownMap('ID', 'Title');
			array_unshift($options, 'Main site');
			$params->push(new CheckboxSetField('Subsites', 'Sites', $options));
		}
		
		$params->push(new PopupDateTimeField('StartDate', 'Start date'));
		$params->push(new PopupDateTimeField('EndDate', 'End date'));
		
		return $fields;
	}
	
	function getCMSActions() {
		$actions = parent::getCMSActions();
		$actions->push(new FormAction('updatereport', 'Search'));
		return $actions;
	}
}
