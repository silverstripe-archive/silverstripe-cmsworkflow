<?php
/**
 * Report showing my deletion requests
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class ThreeStepMyDeletionRequestsSideReport extends SSReport {
	function title() {
		return _t('ThreeStepMyDeletionRequestsSideReport.TITLE',"Removal requests I have made");
	}
	function group() {
		return "Workflow reports";
	}
	function sort() {
		return -100;
	}
	function sourceRecords($params) {
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
	function columns() {
		return array(
			"Title" => array(
				"title" => "Title",
				"link" => true,
			)
		);
	}
	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	}
}

?>