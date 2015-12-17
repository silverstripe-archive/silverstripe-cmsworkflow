<?php

/**
 * Adds the LeftAndMain function to action a pending change
 *
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class LeftAndMainCMSThreeStepWorkflow extends LeftAndMainDecorator
{
    
    public static $allowed_actions = array(
        'cms_publish',
    );
    
    // action
    public function init()
    {
        parent::init();
        CMSBatchActionHandler::register('batchCmsWorkflowPublish', 'BatchPublishPages');
        CMSBatchActionHandler::register('batchCmsWorkflowApprove', 'BatchApprovePages');
        CMSBatchActionHandler::register('forcepublish', 'BatchForcePublishPages');
    }
    public function cms_publish($data, $form)
    {
        return $this->owner->workflowAction('WorkflowRequest', 'publish', $data['ID'], $data['WorkflowComment']);
    }
}
