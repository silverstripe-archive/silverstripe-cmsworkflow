<?php

/**
 * Adds the LeftAndMain function to action a pending change
 *
 * @package cmsworkflow
 * @subpackage threestep
 */
class LeftAndMainCMSThreeStepWorkflow extends LeftAndMainDecorator {
	// action
	public function init() {
		CMSBatchActionHandler::register('batchCmsWorkflowPublish', 'BatchPublishPages');
		CMSBatchActionHandler::register('batchCmsWorkflowApprove', 'BatchApprovePages');
	}
	public function cms_publish($data, $form) {
		return $this->owner->workflowAction('WorkflowRequest', 'publish', $data['ID'], $data['WorkflowComment']);
	}
}

