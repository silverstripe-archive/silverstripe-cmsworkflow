<?php
/**
 * Report showing my deletion requests
 * 
 * @package cmsworkflow
 * @subpackage TwoStep
 */
class TwoStepMyDeletionRequestsSideReport extends SideReport {
	function title() {
		return _t('TwoStepMyDeletionRequestsSideReport.TITLE',"Removal requests I have made");
	}
	function group() {
		return "Workflow reports";
	}
	function sort() {
		return -100;
	}
	function records() {
		// Set stage, otherwise, we won't get any results
		$currentStage = Versioned::current_stage();
		Versioned::reading_stage(Versioned::get_live_stage());
		
		if(Permission::check("ADMIN")) {
			$return = WorkflowTwoStepRequest::get(
				'WorkflowPublicationRequest',
				array('AwaitingApproval')
			);
		} else {
			$return = WorkflowRequest::get_by_approver(
				'WorkflowDeletionRequest',
				Member::currentUser(),
				array('AwaitingApproval')
			);
		}
		
		// Reset stage back to what it was
		Versioned::reading_stage($currentStage);
		
		return $return;
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
		return Object::has_extension('SiteTree', 'SiteTreeCMSTwoStepWorkflow');
	}
}

?>