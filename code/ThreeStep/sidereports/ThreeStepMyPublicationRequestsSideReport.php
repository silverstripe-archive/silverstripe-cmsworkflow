<?php
/**
 * Report showing my publication requests
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class ThreeStepMyPublicationRequestsSideReport extends SideReport {
	function title() {
		return _t('ThreeStepMyPublicationRequestsSideReport.TITLE',"Workflow: my publication requests");
	}
	function records() {
		return WorkflowThreeStepRequest::get_by_author(
			'WorkflowPublicationRequest',
			Member::currentUser(),
			array('AwaitingApproval', 'Approved')
		);
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
		return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	}
}

?>