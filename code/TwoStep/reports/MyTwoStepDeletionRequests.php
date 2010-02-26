<?php
/**
 * Report showing my deletion requests
 * 
 * @package cmsworkflow
 * @subpackage TwoStep
 */
class MyTwoStepDeletionRequests extends SS_Report {
	function title() {
		return _t('MyTwoStepDeletionRequests.TITLE',"Workflow: Awaiting deletion");
	}
	function group() {
		return "Workflow reports";
	}
	function sort() {
		return 100;
	}
	function sourceRecords($params) {
		if(Permission::check("ADMIN")) {
			return WorkflowTwoStepRequest::get(
				'WorkflowDeletionRequest',
				array('AwaitingApproval')
			);
		} else {
			return WorkflowTwoStepRequest::get_by_publisher(
				'WorkflowDeletionRequest',
				Member::currentUser(),
				array('AwaitingApproval')
			);
		}
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
		return Object::has_extension('SiteTree', 'SiteTreeCMSTwoStepWorkflow');
	}
}

?>