<?php
/**
 * Adds a new "sidereport" in the CMS listing all pages a specific author has requested to be changed.
 * 
 * @package cmsworkflow
 */
class MyTwoStepWorkflowRequests extends SS_Report {
	function title() {
		return _t('MyTwoStepWorkflowRequests.TITLE',"Workflow: My requests pending review");
	}
	function group() {
		return "Workflow reports";
	}
	function sort() {
		return 100;
	}
	function sourceRecords($params) {
		return WorkflowTwoStepRequest::get_by_author(
			'WorkflowRequest',
			Member::currentUser(),
			array('AwaitingApproval')
		);
	}
	function columns() {
		return array(
			"Title" => array(
				"title" => "Title",
				"link" => true,
			),
		);
	}
	function canView() {
		return false; Object::has_extension('SiteTree', 'SiteTreeCMSTwoStepWorkflow');
	}
}

?>