<?php
/**
 * @package cmsworkflow
 */
class PagesScheduledForDeletionSideReport_ThisSubsite extends SideReport {
	function title() {
		return _t('MyWorkflowRequestsSideReport.TITLE',"Workflow: pages scheduled for deletion");
	}
	function records() {
		return Versioned::get_by_stage('SiteTree', 'Live', "ExpiryDate > '".SSDateTime::now()."'", 'ExpiryDate DESC');
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
}

class PagesScheduledForDeletionSideReport_AllSubsites extends SideReport {
	function title() {
		return _t('MyWorkflowRequestsSideReport.TITLE',"Workflow: pages scheduled for deletion (all subsites)");
	}
	function records() {
		if (ClassInfo::exists('Subsite')) Subsite::$disable_subsite_filter = true;
		$res = Versioned::get_by_stage('SiteTree', 'Live', "ExpiryDate > '".SSDateTime::now()."'", 'ExpiryDate DESC');
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
}

