<?php

class WorkflowMigrationTask_LatestCompletedWorkflowRequestID extends BuildTask {
	function getTitle() {
		return 'Workflow LatestCompletedWorkflowRequestID';
	}
	function getDescription() {
		return 'Rebuild the LatestCompletedWorkflowRequestID on all pages';
	}
	function run($request) {
		DB::query("UPDATE SiteTree SET LatestCompletedWorkflowRequestID = (SELECT MAX(ID) FROM WorkflowRequest WHERE PageID = SiteTree.ID AND Status = 'Completed')");
		DB::query("UPDATE SiteTree_Live SET LatestCompletedWorkflowRequestID = (SELECT MAX(ID) FROM WorkflowRequest WHERE PageID = SiteTree_Live.ID AND Status = 'Completed')");
		echo "<p>Completed</p>";
	}
}
?>