<?php
/**
 * @package cmsworkflow
 */
class PagesScheduledForPublishingSideReport_ThisSubsite extends SideReport {
	function title() {
		return _t('MyWorkflowRequestsSideReport.TITLE',"Workflow: pages scheduled for publishing");
	}
	function records() {
		$res = DataObject::get('WorkflowPublicationRequest', "Status = 'Scheduled'", 'EmbargoDate DESC');
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
}

class PagesScheduledForPublishingSideReport_AllSubsites extends SideReport {
	function title() {
		return _t('MyWorkflowRequestsSideReport.TITLE',"Workflow: pages scheduled for publishing (all subsites)");
	}
	function records() {
		if (ClassInfo::exists('Subsite')) Subsite::$disable_subsite_filter = true;
		$res = DataObject::get('WorkflowPublicationRequest', "Status = 'Scheduled'", 'EmbargoDate DESC');
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
}

