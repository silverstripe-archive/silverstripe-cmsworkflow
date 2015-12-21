<?php
/**
 * Adds a new "sidereport" in the CMS listing all pages a specific author has requested to be changed.
 * 
 * @package cmsworkflow
 */
class MyTwoStepWorkflowRequests extends SS_Report
{
    public function title()
    {
        return _t('MyTwoStepWorkflowRequests.TITLE', "Workflow: My requests pending review");
    }
    public function group()
    {
        return "Workflow reports";
    }
    public function sort()
    {
        return 100;
    }

    /**
     * This returns the workflow requests outstanding for this user.
     * It does one query against draft for change requests, and another
     * request against live for the deletion requests (which are not in draft
     * any more), and merges the result sets together.
     */
    public function sourceRecords($params)
    {
        increase_time_limit_to(120);
        
        $currentStage = Versioned::current_stage();

        $changes = WorkflowTwoStepRequest::get_by_author(
            'WorkflowPublicationRequest',
            Member::currentUser(),
            array('AwaitingApproval')
        );
        if ($changes) {
            foreach ($changes as $change) {
                $change->RequestType = "Publish";
            }
        }

        Versioned::reading_stage(Versioned::get_live_stage());

        $deletions = WorkflowTwoStepRequest::get_by_author(
            'WorkflowDeletionRequest',
            Member::currentUser(),
            array('AwaitingApproval')
        );
        if ($deletions) {
            foreach ($deletions as $deletion) {
                $deletion->RequestType = "Deletion";
            }
        }

        if ($changes && $deletions) {
            $changes->merge($deletions);
        } elseif ($deletions) {
            $changes = $deletions;
        }

        return $changes;
    }

    public function columns()
    {
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
    public function canView()
    {
        return Object::has_extension('SiteTree', 'SiteTreeCMSTwoStepWorkflow');
    }
}
