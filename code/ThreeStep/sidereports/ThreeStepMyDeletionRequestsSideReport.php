<?php
/**
 * @package cmsworkflow
 */
class ThreeStepMyDeletionRequestsSideReport extends SideReport {
	function title() {
		return _t('DeletionRequestSideReport.TITLE',"Workflow: my deletion requests");
	}
	function records() {
		return WorkflowThreeStepRequest::get_by_author(
			'WorkflowDeletionRequest',
			Member::currentUser(),
			array('AwaitingApproval', 'Approved')
		);
	}
	function fieldsToShow() {
		return array(
			"Title" => array(
				"source" => array("NestedTitle", array("2")),
				"link" => true,
			)
		);
	}
}

?>