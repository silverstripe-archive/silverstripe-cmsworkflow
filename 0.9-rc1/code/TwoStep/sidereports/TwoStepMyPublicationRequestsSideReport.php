<?php
/**
 * Report showing my publication requests
 * 
 * @package cmsworkflow
 * @subpackage TwoStep
 */
class TwoStepMyPublicationRequestsSideReport extends SideReport {
	function title() {
		return _t('TwoStepMyPublicationRequestsSideReport.TITLE',"Publication requests I have made");
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
			return WorkflowTwoStepRequest::get_by_author(
				'WorkflowPublicationRequest',
				Member::currentUser(),
				array('AwaitingApproval')
			);
		}
	}
	function fieldsToShow() {
		return array(
			"Title" => array(
				"source" => "Title",
				"link" => true,
			),
		);
	}
	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSTwoStepWorkflow');
	}
}

?>