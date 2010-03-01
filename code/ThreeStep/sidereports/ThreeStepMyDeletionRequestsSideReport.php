<?php
/**
 * Report showing my deletion requests
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
// @codeCoverageIgnoreStart
class ThreeStepMyDeletionRequestsSideReport extends SS_Report {
	function title() {
		return _t('ThreeStepMyDeletionRequestsSideReport.TITLE',"My deletion requests");
	}
	
	function group() {
		return _t('WorkflowRequest.WORKFLOW', 'Workflow');
	}
	
	function sourceRecords($params) {
		// Set stage, otherwise, we won't get any results
		$currentStage = Versioned::current_stage();
		Versioned::reading_stage(Versioned::get_live_stage());
		$res = WorkflowThreeStepRequest::get_by_author(
			'WorkflowDeletionRequest',
			Member::currentUser(),
			array('AwaitingApproval', 'Approved')
		);
		// Reset stage back to what it was
		Versioned::reading_stage($currentStage);

		// Add WFRequestedWhen column
		$doSet = new DataObjectSet();
		if ($res) {
			foreach ($res as $result) {
				if ($wf = $result->openWorkflowRequest()) {
					$result->WFRequestedWhen = $wf->Created;
					$doSet->push($result);
				}
			}
		}
	
		return $doSet;
	}
	function columns() {
		return array(
			"Title" => array(
				"link" => true,
			),
			"WFRequestedWhen" => array(
				"formatting" => 'Requested on $value',
				'casting' => 'SS_Datetime->Full'
			),
		);
	}
	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	}
}
// @codeCoverageIgnoreEnd
?>