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
		
		if ($this->owner->ClassName == 'WorkflowDeletionRequest') {
			if (isset($_REQUEST['DeletionScheduling']) && $_REQUEST['DeletionScheduling'] == 'scheduled') {
				// Update SiteTree_Live directly, rather than doing a publish
				// Because otherwise, unauthorized edits could be pushed live.
				
				list($day, $month, $year) = explode('/', $_REQUEST['ExpiryDate']['Date']);
				$expiryTimestamp = Convert::raw2sql(date('Y-m-d H:i:s', strtotime("$year-$month-$day {$_REQUEST['ExpiryDate']['Time']}")));
				$pageID = $this->owner->Page()->ID;
			
				if ($expiryTimestamp)
				
				DB::query("UPDATE SiteTree_Live SET ExpiryDate = '$expiryTimestamp' WHERE ID = $pageID");
			}
		}

				$this->owner->ApproverID = $member->ID;
				$this->owner->Status = 'Completed';
				$this->owner->write();
				
				$this->owner->addNewChange($comment, $this->owner->Status, $member);
				if($notify) $this->notifyApproved($comment);
				return _t('WorkflowDeletionRequest.SETEXPIRY','Set Expiry date. Emailed %s');
			}
		}

		$this->owner->PublisherID = $member->ID;
		$this->owner->Status = 'Approved';
		$this->owner->write();
		
		$this->owner->setSchedule();
		
		// Action it immediately... if it's not scheduled
		if ($this->owner->Status != 'Scheduled') {
			$this->owner->publish($comment, $member, $notify);

		// Only do these actions here if we've scheduled the action; otherwise publish() will
		// comment for us
		} else {
			$this->owner->addNewChange($comment, $this->owner->Status, $member);
		}

		if($notify) $this->notifyApproved($comment);
		
		return _t('SiteTreeCMSWorkflow.APPROVEDANDPUBLISHMESSAGE','Approved request and published changes to live version. Emailed %s.');
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
		
		if (WorkflowRequest::should_send_alert($this->owner->class, 'publish', 'publisher')) {
			$publishers = $this->owner->Page()->PublisherMembers();
			foreach($publishers as $publisher){
				// Notify publishers other than the one who is logged in 
				if(Member::currentUserID() != $publisher->ID) {
					$this->owner->sendNotificationEmail(
						Member::currentUser(), // sender
						$publisher, // recipient
						$comment,
						_t('WorkflowRequest.APPROVED_CHANGES', 'approved changes')
					);
				}
			}
		}

		if (WorkflowRequest::should_send_alert($this->owner->class, 'publish', 'author')) {
			$this->owner->sendNotificationEmail(
				Member::currentUser(), // sender
				$author, // recipient
				$comment,
				_t('WorkflowRequest.APPROVED_CHANGES', 'approved changes')
			);
		}
	}
	
	function notifyComment($comment) {
		// Comment recipients cover everyone except the person making the comment
		$commentRecipients = array();
		if (WorkflowRequest::should_send_alert($this->owner->class, 'comment', 'author')) {
			if(Member::currentUserID() != $this->owner->Author()->ID) $commentRecipients[] = $this->owner->Author();
		}
		if (WorkflowRequest::should_send_alert($this->owner->class, 'comment', 'publisher')) {
			$publishers = $this->owner->Page()->PublisherMembers();
			foreach($publishers as $publisher){
				if(Member::currentUserID() != $publisher->ID) $commentRecipients[] = $publisher;
			}
		}

		foreach($commentRecipients as $recipient) {
			$this->owner->sendNotificationEmail(
				Member::currentUser(), // sender
				$recipient, // recipient
				$comment,
				_t('WorkflowRequest.ADDED_COMMENT', 'added a comment')
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
		
		if (WorkflowRequest::should_send_alert($this->owner->class, 'request', 'publisher')) {
			foreach($publishers as $publisher){
				$this->owner->sendNotificationEmail(
					$author, // sender
					$publisher, // recipient
					$comment,
					$this->owner->class == 'WorkflowDeletionRequest' ? 
						_t('WorkflowRequest.REQUESTED_DELETION', 'requested deletion') :
						_t('WorkflowRequest.REQUESTED_PUBLICATION', 'requested publication')
				);
			}
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
			$actions['cms_approve'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_PUBLISH", "Publish");
			if (get_class($this->owner) != 'WorkflowDeletionRequest') $actions['cms_requestedit'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_REQUESTEDIT", "Request edit");
			if (WorkflowRequest::$allow_deny) $actions['cms_deny'] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_DENY","Deny and revert");
		} else if($this->owner->Status == 'AwaitingEdit' && $this->owner->Page()->canEdit()) {
			// @todo this couples this class to its subclasses. :-(
			$requestAction = (get_class($this->owner) == 'WorkflowDeletionRequest') ? 'cms_requestdeletefromlive' : 'cms_requestpublication';
			$actions[$requestAction] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_RESUBMIT", "Re-submit");
		}
		
		if ($this->owner->Page()->canEdit()) {
			$actions['cms_cancel'] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_CANCEL","Cancel workflow");
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
	
	public static function apply_alerts() {
		WorkflowRequest::$alerts = array(
			'WorkflowPublicationRequest' => array(
				'request' => array(
					'publisher' => true
				),
				'publish' => array(
					'author' => true,
					'publisher' => true
				),
				'deny' => array(
					'author' => true
				),
				'cancel' => array(
					'author' => true
				),
				'comment' => array(
					'author' => true,
					'publisher' => true
				)
			),
			'WorkflowDeletionRequest' => array(
				'request' => array(
					'publisher' => true
				),
				'publish' => array(
					'author' => true,
					'publisher' => true
				),
				'deny' => array(
					'author' => true
				),
				'cancel' => array(
					'author' => true
				),
				'comment' => array(
					'author' => true,
					'publisher' => true
				)
			)
		);
	}
	
	function IsTwoStep() {
		return true;
	}
}