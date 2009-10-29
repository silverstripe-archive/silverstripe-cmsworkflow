<?php
/**
 * Report showing my deletion requests
 * 
 * @package cmsworkflow
 * @subpackage TwoStep
 */
class MyTwoStepDeletionRequestsSideReport extends SideReport {
	function title() {
		return _t('MyTwoStepDeletionRequestsSideReport.TITLE',"Workflow: Awaiting deletion");
	}
	function group() {
		return "Workflow reports";
	}
	function sort() {
		return -100;
	}
	function records() {
		return WorkflowTwoStepRequest::get_by_publisher(
			'WorkflowDeletionRequest',
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
		return false;
		return Object::has_extension('SiteTree', 'SiteTreeCMSTwoStepWorkflow');
	}
}

?>