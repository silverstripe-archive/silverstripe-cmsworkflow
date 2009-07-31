<?php
/**
 * @package cmsworkflow
 */
class ThreeStepWorkflowRequestsNeedingApprovalSideReport extends SideReport {
	function title() {
		return _t('MyWorkflowRequestsSideReport.TITLE',"Workflow: requests I need to approve");
	}
	function records() {
		return WorkflowThreeStepRequest::get_by_approver(
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