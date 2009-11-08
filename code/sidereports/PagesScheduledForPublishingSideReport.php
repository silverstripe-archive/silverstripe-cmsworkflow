<?php
/**
 * Report to show pages that will be published soon
 *
 * @package cmsworkflow
 * @subpackage reports
 */
class PagesScheduledForPublishingSideReport extends SideReport {
	function title() {
		return _t('PagesScheduledForPublishingSideReport.TITLE',"Pages scheduled for publishing");
	}
	function group() {
		return "Workflow reports";
	}
	function sort() {
		return 900;
	}
	function records() {
		if (ClassInfo::exists('Subsite') && isset($this->params['AllSubsites'])) {
			$oldSSFilterState = Subsite::$disable_subsite_filter;
			Subsite::$disable_subsite_filter = true;
		}
		
		$startDate = isset($this->params['StartDate']) ? $this->params['StartDate'] : null;
		$endDate = isset($this->params['EndDate']) ? $this->params['EndDate'] : null;
		if ($startDate && $endDate) {
			$where = "EmbargoDate >= '".Convert::raw2sql($startDate)."' AND EmbargoDate <= '".Convert::raw2sql($endDate)."'";
		} else if ($startDate && !$endDate) {
			$where = "EmbargoDate >= '".Convert::raw2sql($startDate)."'";
		} else if (!$startDate && $endDate) {
			$where = "EmbargoDate <= '".Convert::raw2sql($endDate)."'";
		} else {
			$where = "EmbargoDate >= '".SS_Datetime::now()->URLDate()."'";
		}
		
		$res = DataObject::get('SiteTree', '"WorkflowRequest"."Status" = \'Scheduled\' AND '.$where, null, "LEFT JOIN WorkflowRequest on WorkflowRequest.PageID = SiteTree.ID");
		
		$doSet = new DataObjectSet();
		if (!$res) return false;
		foreach ($res as $page) {
			$page->WFTimeOfPublishing = $page->openWorkflowRequest()->EmbargoDate;
			$doSet->push($page);
		}
		
		if (ClassInfo::exists('Subsite') && isset($this->params['AllSubsites'])) {
			Subsite::$disable_subsite_filter = $oldSSFilterState;
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
		$fieldset = new FieldSet(
			new DateField('StartDate', 'Start date (YYYY-MM-DD HH:mm:ss)'),
			new DateField('EndDate', 'End date (YYYY-MM-DD HH:mm:ss)')
		);
		if (ClassInfo::exists('Subsite')) $fieldset->push(new CheckboxField('AllSubsites', 'All subsites'));
		return $fieldset;
	}
}