<?php
/**
 * Adds a new "sidereport" in the CMS listing all pages a specific author has requested to be changed.
 * 
 * @package cmsworkflow
 */
class MyTwoStepWorkflowRequests extends SS_Report {
	function title() {
		return _t('MyTwoStepWorkflowRequests.TITLE',"Workflow: My requests pending review");
	}
	function group() {
		return "Workflow reports";
	}
	function sort() {
		return 100;
	}

	/**
	 * This returns the workflow requests outstanding for this user.
	 * It does one query against draft for change requests, and another
	 * request against live for the deletion requests (which are not in draft
	 * any more), and merges the result sets together.
	 */
	function sourceRecords($params) {
		$currentStage = Versioned::current_stage();

		$changes = WorkflowTwoStepRequest::get_by_author(
			'WorkflowPublicationRequest',
			Member::currentUser(),
			array('AwaitingApproval')
		);
		if ($changes) foreach ($changes as $change) $change->RequestType = "Publish";

		Versioned::reading_stage(Versioned::get_live_stage());

		$deletions = WorkflowTwoStepRequest::get_by_author(
			'WorkflowDeletionRequest',
			Member::currentUser(),
			array('AwaitingApproval')
		);
		if ($deletions) foreach ($deletions as $deletion) $deletion->RequestType = "Deletion";

		if ($changes && $deletions) $changes->merge($deletions);
		else if ($deletions) $changes = $deletions;

		return $changes;
	}

	function columns() {
		return array(
			"RequestType" => array(
				"title" => "Type",
				"link" => false
			),
			"Title" => array(
				"title" => "Title",
				"link" => true,
			)
		);
	}
	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSTwoStepWorkflow');
	}
}

?>