<?php
/**
 * Report showing my publication requests
 * 
 * @package cmsworkflow
 * @subpackage TwoStep
 */
class MyTwoStepPublicationRequests extends SS_Report {
	function title() {
		return _t('MyTwoStepPublicationRequests.TITLE',"Workflow: Awaiting publication");
	}
	function sourceRecords($params) {
		if(Permission::check("ADMIN")) {
			return WorkflowTwoStepRequest::get(
				'WorkflowPublicationRequest',
				array('AwaitingApproval')
			);
		} else {
			return WorkflowTwoStepRequest::get_by_publisher(
				'WorkflowPublicationRequest',
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