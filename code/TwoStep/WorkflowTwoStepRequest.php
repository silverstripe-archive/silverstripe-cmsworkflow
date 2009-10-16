<?php

/**
 * ThreeStep, where an item is actioned immediately.
 *
 * @package cmsworkflow
 * @subpackage twostep
 * @author Tom Rix
 */
class WorkflowTwoStepRequest extends WorkflowRequestDecorator {
	function approve($comment, $member = null, $notify = true) {
		if(!$member) $member = Member::currentUser();
		if(!$this->owner->Page()->canPublish($member)) {
			return false;
		}

		$this->owner->PublisherID = $member->ID;
		$this->owner->Status = 'Approved';
		$this->owner->write();
		
		$this->owner->setSchedule();
		
		$this->owner->addNewChange($comment, $this->owner->Status, $member);
		if($notify) $this->notifyApproved($comment);
		
		// Action it immediately... if it's not scheduled
		if ($this->owner->Status != 'Scheduled') {
			$this->owner->publish($comment, $member, $notify);
		}
		
		return _t('SiteTreeCMSWorkflow.PUBLISHMESSAGE','Approved request and published changes to live version. Emailed %s.');
	}
	
	function saveAndPublish($comment, $member = null, $notify = true) {
		return $this->approve($comment, $member, $notify);
	}
	
	function notifyApproved($comment) {
		$author = $this->owner->Author();
		$subject = sprintf(
			_t("{$this->owner->class}.EMAIL_SUBJECT_APPROVED"),
			$this->owner->Page()->Title
		);
		
		$publishers = $this->owner->Page()->PublisherMembers();
		foreach($publishers as $publisher){
			// Notify publishers other than the one who is logged in 
			if(Member::currentUserID() != $publisher->ID) {
				$this->owner->sendNotificationEmail(
					Member::currentUser(), // sender
					$publisher, // recipient
					_t("{$this->owner->class}.EMAIL_SUBJECT_APPROVED"),
					_t("{$this->owner->class}.EMAIL_PARA_APPROVED"),
					$comment,
					'WorkflowGenericEmail'
				);
			}
		}

		$this->owner->sendNotificationEmail(
			Member::currentUser(), // sender
			$author, // recipient
			_t("{$this->owner->class}.EMAIL_SUBJECT_APPROVED"),
			_t("{$this->owner->class}.EMAIL_PARA_APPROVED"),
			$comment,
			'WorkflowGenericEmail'
		);
	}
	
	function notifyComment($comment) {
		// Comment recipients cover everyone except the person making the comment
		$commentRecipients = array();
		if(Member::currentUserID() != $this->owner->Author()->ID) $commentRecipients[] = $this->owner->Author();
		$publishers = $this->owner->Page()->PublisherMembers();
		foreach($publishers as $publisher){
			if(Member::currentUserID() != $publisher->ID) $commentRecipients[] = $publisher;
		}

		foreach($commentRecipients as $recipient) {
			$this->owner->sendNotificationEmail(
				Member::currentUser(), // sender
				$recipient, // recipient
				_t("{$this->owner->class}.EMAIL_SUBJECT_COMMENT"),
				_t("{$this->owner->class}.EMAIL_PARA_COMMENT"),
				$comment,
				'WorkflowGenericEmail'
			);
		}
	}
	
	/**
	 * Notify any publishers assigned to this page when a new request
	 * is lodged.
	 */
	public function notifyAwaitingApproval($comment) {
		$publishers = $this->owner->Page()->PublisherMembers();
		$author = $this->owner->Author();
		
		foreach($publishers as $publisher){
			$this->owner->sendNotificationEmail(
				$author, // sender
				$publisher, // recipient
				_t("{$this->owner->class}.EMAIL_SUBJECT_AWAITINGAPPROVAL"),
				_t("{$this->owner->class}.EMAIL_PARA_AWAITINGAPPROVAL"),
				$comment,
				'WorkflowGenericEmail'
			);
		}
	}
	
	/**
	 * Return the actions that can be performed on this workflow request.
	 * @return array The key is a LeftAndMainCMSWorkflow action, and the value is a label
	 * for the buton.
	 * @todo There's not a good separation between model and control in this stuff.
	 */
	function WorkflowActions() {
		$actions = array();
		
		if($this->owner->Status == 'AwaitingApproval' && $this->owner->Page()->canPublish()) {
			$actions['cms_approve'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_APPROVE", "Approve");
			$actions['cms_requestedit'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_REQUESTEDIT", "Request edit");
			if (self::$allow_deny) $actions['cms_deny'] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_DENY","Deny");
		} else if($this->owner->Status == 'AwaitingEdit' && $this->owner->Page()->canEdit()) {
			// @todo this couples this class to its subclasses. :-(
			$requestAction = (get_class($this->owner) == 'WorkflowDeletionRequest') ? 'cms_requestdeletefromlive' : 'cms_requestpublication';
			$actions[$requestAction] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_RESUBMIT", "Re-submit");
		}
		
		if ($this->owner->Page()->canEdit()) {
			$actions['cms_cancel'] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_CANCEL","Cancel");
		}
		$actions['cms_comment'] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_COMMENT", "Comment");

		return $actions;
	}
	
	public static function get_by_publisher($class, $publisher, $status = null) {
		return WorkflowRequest::get_by_publisher($class, $publisher, $status);
	}
	
	public static function get_by_author($class, $author, $status = null) {
		return WorkflowRequest::get_by_author($class, $author, $status);
	}
	
	public static function get($class, $status = null) {
		return WorkflowRequest::get($class, $status);
	}
}