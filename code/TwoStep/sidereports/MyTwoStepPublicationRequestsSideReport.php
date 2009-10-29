<?php
/**
 * Report showing my publication requests
 * 
 * @package cmsworkflow
 * @subpackage TwoStep
 */
class MyTwoStepPublicationRequestsSideReport extends SideReport {
	function title() {
		return _t('MyTwoStepPublicationRequestsSideReport.TITLE',"Workflow: Awaiting publication");
	}
	function group() {
		return "Workflow reports";
	}
	function sort() {
		return -200;
	}
	function records() {
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