<?php
/**
 * Adds a new "sidereport" in the CMS listing all pages a specific author has requested to be changed.
 * 
 * @package cmsworkflow
 */
class MyTwoStepWorkflowRequestsSideReport extends SideReport {
	function title() {
		return _t('MyTwoStepWorkflowRequestsSideReport.TITLE',"Workflow: My requests pending review");
	}
	function records() {
		return WorkflowTwoStepRequest::get_by_author(
			'WorkflowRequest',
			Member::currentUser(),
			array('AwaitingApproval')
		);
	}
	function fieldsToShow() {
		return array(
			"Title" => array(
				"source" => array("NestedTitle", array("2")),
				"link" => true,
			),
		);
	}
	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSTwoStepWorkflow');
	}
}

?>