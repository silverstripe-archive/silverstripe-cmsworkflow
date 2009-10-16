<?php
/**
 * @package cmsworkflow
 */
class PagesScheduledForDeletionSideReport_ThisSubsite extends SideReport {
	function title() {
		return _t('MyWorkflowRequestsSideReport.TITLE',"Workflow: pages scheduled for deletion");
	}
	function records() {
		$startDate = isset($this->params['StartDate']) ? $this->params['StartDate'] : null;
		$endDate = isset($this->params['EndDate']) ? $this->params['EndDate'] : null;
		if ($startDate && $endDate) {
			$where = "ExpiryDate >= '".Convert::raw2sql($startDate)."' AND ExpiryDate < '".Convert::raw2sql($endDate)."'";
		} else if ($startDate && !$endDate) {
			$where = "ExpiryDate >= '".Convert::raw2sql($startDate)."'";
		} else if (!$startDate && $endDate) {
			$where = "ExpiryDate <= '".Convert::raw2sql($endDate)."'";
		} else {
			$where = "ExpiryDate >= '".SSDatetime::now()->URLDate()."'";
		}

		return Versioned::get_by_stage('SiteTree', 'Live', $where, 'ExpiryDate DESC');
	}
	function fieldsToShow() {
		return array(
			"Title" => array(
				"source" => array("NestedTitle", array("2")),
				"link" => true,
			),
			"Requester" => array(
				"prefix" => 'Will be deleted at ',
				"source" => "ExpiryDate",
			)
		);
	}
	function getParameterFields() {
		return new FieldSet(
			new DateField('StartDate', 'Start date (YYYY-MM-DD)'),
			new DateField('EndDate', 'End date (YYYY-MM-DD)')
		);
	}
}

class PagesScheduledForDeletionSideReport_AllSubsites extends SideReport {
	function title() {
		return _t('MyWorkflowRequestsSideReport.TITLE',"Workflow: pages scheduled for deletion (all subsites)");
	}
	function records($params = null) {
		if (ClassInfo::exists('Subsite')) Subsite::$disable_subsite_filter = true;
		
		$startDate = isset($this->params['StartDate']) ? $this->params['StartDate'] : null;
		$endDate = isset($this->params['EndDate']) ? $this->params['EndDate'] : null;
		if ($startDate && $endDate) {
			$where = "ExpiryDate >= '".Convert::raw2sql($startDate)."' AND ExpiryDate < '".Convert::raw2sql($endDate)."'";
		} else if ($startDate && !$endDate) {
			$where = "ExpiryDate >= '".Convert::raw2sql($startDate)."'";
		} else if (!$startDate && $endDate) {
			$where = "ExpiryDate <= '".Convert::raw2sql($endDate)."'";
		} else {
			$where = "ExpiryDate >= '".SSDatetime::now()->URLDate()."'";
		}
		
		$res = Versioned::get_by_stage('SiteTree', 'Live', $where, 'ExpiryDate DESC');
		
		if (ClassInfo::exists('Subsite')) Subsite::$disable_subsite_filter = false;
		return $res;
	}
	function fieldsToShow() {
		return array(
			"Title" => array(
				"source" => array("NestedTitle", array("2")),
				"link" => true,
			),
			"Requester" => array(
				"prefix" => 'Will be deleted at ',
				"source" => "ExpiryDate",
			)
		);
	}
	function getParameterFields() {
		return new FieldSet(
			new DateField('StartDate', 'Start date (YYYY-MM-DD)'),
			new DateField('EndDate', 'End date (YYYY-MM-DD)')
		);
	}
}

