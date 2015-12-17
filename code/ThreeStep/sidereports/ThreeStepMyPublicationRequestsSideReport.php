<?php
/**
 * Report showing my publication requests
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
// @codeCoverageIgnoreStart
class ThreeStepMyPublicationRequestsSideReport extends SS_Report
{
    public function title()
    {
        return _t('ThreeStepMyPublicationRequestsSideReport.TITLE', "My publication requests");
    }

    public function group()
    {
        return _t('WorkflowRequest.WORKFLOW', 'Workflow');
    }

    public function sourceRecords($params)
    {
        $res = WorkflowThreeStepRequest::get_by_author(
            'WorkflowPublicationRequest',
            Member::currentUser(),
            array('AwaitingApproval', 'Approved')
        );
        
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
    public function columns()
    {
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
    public function canView()
    {
        return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
    }
}
// @codeCoverageIgnoreEnd
;
