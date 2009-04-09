<?php
class LeftAndMainCMSWorkflow extends LeftAndMainDecorator {
	
	public static $allowed_actions = array(
		'cms_requestpublication',
		'cms_requestdeletefromlive',
		'cms_denypublication',
		'cms_denydeletion'
	);
	
	function init() {
		Requirements::javascript('cmsworkflow/javascript/LeftAndMainCMSWorkflow.js');
		RSSFeed::linkToFeed(Director::absoluteURL('admin/cms/changes.rss'), 'All content changes');
	}
	
	// Request
	
	/**
	 * Handler for the CMS button
	 */
	public function cms_requestpublication($data, $form) {
		return $this->workflowAction('WorkflowPublicationRequest', 'request', $data['ID'], $data['WorkflowComment'],
			_t('SiteTreeCMSWorkflow.REQUEST_PUBLICATION_SUCCESS_MESSAGE','Emailed %s requesting publication')
		);
	}
	
	public function cms_requestdeletefromlive($data, $form) {
		return $this->workflowAction('WorkflowDeletionRequest', 'request', $data['ID'], $data['WorkflowComment'],
			_t('SiteTreeCMSWorkflow.REQUEST_DELETEFROMLIVE_SUCCESS_MESSAGE','Emailed %s requesting deletion')
		);
	}

	// Approve
	public function cms_approve($data, $form) {
		return $this->workflowAction('WorkflowRequest', 'approve', $data['ID'], $data['WorkflowComment'],
			_t('SiteTreeCMSWorkflow.PUBLISHMESSAGE','Approved request and published to the live version. Emailed %s.')
		);
	}
	public function cms_publishwithcomment($data, $form) {
		return $this->workflowAction('WorkflowPublicationRequest', 'approve', $data['ID'], $data['WorkflowComment'],
			_t('SiteTreeCMSWorkflow.PUBLISHMESSAGE','Approved request and published changes to live version. Emailed %s.')
		);
	}

	// Request edit
	public function cms_requestedit($data, $form) {
		return $this->workflowAction('WorkflowRequest', 'requestedit', $data['ID'], $data['WorkflowComment'],
			_t('SiteTreeCMSWorkflow.DENYPUBLICATION_MESSAGE','Denied workflow request, and reset content. Emailed %s')
		);
	}

	// Deny - ie, cancel the workflow change
	public function cms_deny($data, $form) {
		return $this->workflowAction('WorkflowRequest', 'deny', $data['ID'], $data['WorkflowComment'],
			_t('SiteTreeCMSWorkflow.DENYPUBLICATION_MESSAGE','Denied workflow request, and reset content. Emailed %s')
		);
	}
	
	// Comment (no workflow status change)
	public function cms_comment($data, $form) {
		return $this->workflowAction('WorkflowRequest', 'comment', $data['ID'], $data['WorkflowComment'],
			_t('SiteTreeCMSWorkflow.COMMENT_MESSAGE','Commented on this workflow request. Emailed %s.')
		);
	}

	/**
	 * Process a workflow action.
	 * @param string $workflowClass The sub-class of WorkflowRequest that is expected.
	 * @param string $actionName The action method to call on the given WorkflowRequest objec.t
	 * @param int $id The ID# of the page.
	 * @param string $comment The comment to attach.
	 * @param string $successMessage The message to show on success.
	 */
	function workflowAction($workflowClass,  $actionName, $id, $comment, $successMessage) {
		if(is_numeric($id)) {
			$page = DataObject::get_by_id("SiteTree", $id);
			if(!$page) $page = Versioned::get_one_by_stage("SiteTree", "Live", "`SiteTree`.ID = $id");
			if(!$page) return new HTTPResponse("Can't find Page #$id", 400);
		} else {
			return new HTTPResponse("Bad ID", 400);
		}
		
		if($request = $page->openOrNewWorkflowRequest($workflowClass)) {
			if($request->$actionName($comment)) {
				FormResponse::get_page($id);
		
				// gather members for status output
				$emails = $page->PublisherMembers()->column('Email');
				FormResponse::status_message(sprintf($successMessage, implode(", ", $emails)), 'good');
				return FormResponse::respond();
			}
		}

		// Failure
		FormResponse::status_message(_t('SiteTreeCMSWorkflow.WORKFLOW_ACTION_FAILED', 
			"There was an error when processing your workflow request."), 'bad');
		return FormResponse::respond();
	}
	

}
?>