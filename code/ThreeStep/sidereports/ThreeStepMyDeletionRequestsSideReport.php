<?php
/**
 * Report showing my deletion requests
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class ThreeStepMyDeletionRequestsSideReport extends SideReport {
	function title() {
		return _t('ThreeStepMyDeletionRequestsSideReport.TITLE',"Workflow: my deletion requests");
	}
	function records() {
		// Set stage, otherwise, we won't get any results
		$currentStage = Versioned::current_stage();
		Versioned::reading_stage(Versioned::get_live_stage());
		$result = WorkflowThreeStepRequest::get_by_author(
			'WorkflowDeletionRequest',
			Member::currentUser(),
			array('AwaitingApproval', 'Approved')
		);
		// Reset stage back to what it was
		Versioned::reading_stage($currentStage);
		return $result;
	}
	function fieldsToShow() {
		return array(
			"Title" => array(
				"source" => array("NestedTitle", array("2")),
				"link" => true,
			)
		);
	}
	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	}
}

?>