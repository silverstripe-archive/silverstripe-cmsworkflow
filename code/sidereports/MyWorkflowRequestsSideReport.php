<?php
/**
 * Adds a new "sidereport" in the CMS listing all pages a specific author has requested to be changed.
 * 
 * @package cmsworkflow
 */
class MyWorkflowRequestsSideReport extends SideReport {
	function title() {
		return _t('MyWorkflowRequestsSideReport.TITLE',"Workflow: My requests pending review");
	}
	function records() {
		return WorkflowRequest::get_by_author(
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
}

?>