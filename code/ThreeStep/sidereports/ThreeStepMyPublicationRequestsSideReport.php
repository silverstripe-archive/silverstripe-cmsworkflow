<?php
/**
 * @package cmsworkflow
 */
class ThreeStepMyPublicationRequestsSideReport extends SideReport {
	function title() {
		return _t('PublicationRequestSideReport.TITLE',"Workflow: my publication requests");
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
}

?>