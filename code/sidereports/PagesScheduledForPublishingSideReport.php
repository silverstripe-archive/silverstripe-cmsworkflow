<?php
/**
 * Report to show pages that will be published soon
 *
 * @package cmsworkflow
 * @subpackage reports
 */
class PagesScheduledForPublishingSideReport_ThisSubsite extends SideReport {
	function title() {
		return _t('PagesScheduledForPublishingSideReport.TITLE',"Workflow: pages scheduled for publishing");
	}
	function records() {
		$startDate = isset($this->params['StartDate']) ? $this->params['StartDate'] : null;
		$endDate = isset($this->params['EndDate']) ? $this->params['EndDate'] : null;
		if ($startDate && $endDate) {
			$where = "EmbargoDate >= '".Convert::raw2sql($startDate)."' AND EmbargoDate < '".Convert::raw2sql($endDate)."'";
		} else if ($startDate && !$endDate) {
			$where = "EmbargoDate >= '".Convert::raw2sql($startDate)."'";
		} else if (!$startDate && $endDate) {
			$where = "EmbargoDate <= '".Convert::raw2sql($endDate)."'";
		} else {
			$where = "EmbargoDate >= '".SSDatetime::now()->URLDate()."'";
		}
		
		$res = DataObject::get('WorkflowPublicationRequest', "Status = 'Scheduled' AND $where", 'EmbargoDate DESC');
		$doSet = new DataObjectSet();
		if (!$res) return false;
		foreach ($res as $result) {
			$page = $result->Page();
			$page->WFTimeOfPublishing = $result->EmbargoDate;
			$doSet->push($page);
		}
		return $doSet;
	}
	function fieldsToShow() {
		return array(
			"Title" => array(
				"source" => array("NestedTitle", array("2")),
				"link" => true,
			),
			"Requester" => array(
				"prefix" => 'Will be published at ',
				"source" => "WFTimeOfPublishing",
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

class PagesScheduledForPublishingSideReport_AllSubsites extends SideReport {
	function title() {
		return _t('PagesScheduledForPublishingSideReport.TITLE_ALLSUBSITES',"Workflow: pages scheduled for publishing (all subsites)");
	}
	function records($params = null) {
		$startDate = isset($this->params['StartDate']) ? $this->params['StartDate'] : null;
		$endDate = isset($this->params['EndDate']) ? $this->params['EndDate'] : null;
		if ($startDate && $endDate) {
			$where = "EmbargoDate >= '".Convert::raw2sql($startDate)."' AND EmbargoDate < '".Convert::raw2sql($endDate)."'";
		} else if ($startDate && !$endDate) {
			$where = "EmbargoDate >= '".Convert::raw2sql($startDate)."'";
		} else if (!$startDate && $endDate) {
			$where = "EmbargoDate <= '".Convert::raw2sql($endDate)."'";
		} else {
			$where = "EmbargoDate >= '".SSDatetime::now()->URLDate()."'";
		}
		
		if (ClassInfo::exists('Subsite')) Subsite::$disable_subsite_filter = true;
		$res = DataObject::get('WorkflowPublicationRequest', "Status = 'Scheduled' AND $where", 'EmbargoDate DESC');
		$doSet = new DataObjectSet();
		if (!$res) return false;
		foreach ($res as $result) {
			$page = $result->Page();
			$page->WFTimeOfPublishing = $result->EmbargoDate;
			$doSet->push($page);
		}
		if (ClassInfo::exists('Subsite')) Subsite::$disable_subsite_filter = false;
		return $doSet;
	}
	function fieldsToShow() {
		return array(
			"Title" => array(
				"source" => array("NestedTitle", array("2")),
				"link" => true,
			),
			"Requester" => array(
				"prefix" => 'Will be published at ',
				"source" => "WFTimeOfPublishing",
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

