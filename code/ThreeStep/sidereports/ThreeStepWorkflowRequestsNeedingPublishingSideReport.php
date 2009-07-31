<?php
/**
 * @package cmsworkflow
 */
class ThreeStepWorkflowRequestsNeedingPublishingSideReport extends SideReport {
	function title() {
		return _t('MyWorkflowRequestsSideReport.TITLE',"Workflow: requests I need to publish");
	}
	function records() {
		return WorkflowThreeStepRequest::get_by_publisher(
			'WorkflowRequest',
			Member::currentUser(),
			array('Approved')
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