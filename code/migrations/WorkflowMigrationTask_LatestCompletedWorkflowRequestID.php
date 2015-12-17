<?php

class WorkflowMigrationTask_LatestCompletedWorkflowRequestID extends BuildTask
{
    public function getTitle()
    {
        return 'Workflow LatestCompletedWorkflowRequestID';
    }
    public function getDescription()
    {
        return 'Rebuild the LatestCompletedWorkflowRequestID on all pages';
    }
    public function run($request)
    {
        DB::query("UPDATE SiteTree SET LatestCompletedWorkflowRequestID = (SELECT MAX(ID) FROM WorkflowRequest WHERE PageID = SiteTree.ID AND Status = 'Completed')");
        DB::query("UPDATE SiteTree_Live SET LatestCompletedWorkflowRequestID = (SELECT MAX(ID) FROM WorkflowRequest WHERE PageID = SiteTree_Live.ID AND Status = 'Completed')");
        echo "<p>Completed</p>";
    }
}
