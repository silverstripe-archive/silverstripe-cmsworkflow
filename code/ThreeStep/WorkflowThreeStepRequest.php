<?php

/**
 * ThreeStep, where an item is not actioned immediately.
 *
 * @package cmsworkflow
 * @subpackage threestep
 * @author Tom Rix
 */
class WorkflowThreeStepRequest extends WorkflowRequestDecorator {
	function approve($comment, $member = null, $notify = true) {
		if(!$member) $member = Member::currentUser();
		if(!$this->owner->Page()->canApprove($member)) {
			return false;
		}
	
		$this->owner->ApproverID = $member->ID;
		$this->owner->Status = 'Approved';
		$this->owner->write();

		$this->owner->setSchedule();

		$this->owner->addNewChange($comment, $this->owner->Status, $member);
		if($notify) $this->notifyApproved($comment);
		
		// The request is now approved, but we haven't published it yet
		// cause that's not how we roll here in ThreeStepRequest
		
		return _t('SiteTreeCMSWorkflow.APPROVEMESSAGE','Approved request. Emailed %s.');
	}
	
	function publish($comment, $member = null, $notify = true) {
		if(!$member) $member = Member::currentUser();
		if(!$this->owner->Page()->canPublish($member)) {
			return false;
		}
		
		if ($notify) {
			// Notify?
		}
		
		return $this->owner->publish($comment, $member, $notify);
	}
	
	function saveAndPublish($comment, $member = null, $notify = true) {
		$this->approve($comment, $member, $notify);
		$this->publish($comment, $member, $notify);
		return _t('SiteTreeCMSWorkflow.PUBLISHMESSAGE','Approved request and published changes to live version. Emailed %s.');
	}
	
	function notifyApproved($comment) {
		$author = $this->owner->Author();
		$subject = sprintf(
			_t("{$this->owner->class}.EMAIL_SUBJECT_APPROVED"),
			$this->owner->Page()->Title
		);
		
		if (WorkflowRequest::should_send_alert(__CLASS__, 'approve', 'publisher')) {
			$publishers = $this->owner->Page()->PublisherMembers();
			foreach($publishers as $publisher){
				// Notify publishers other than the one who is logged in 
				if(Member::currentUserID() != $publisher->ID) {
					$this->owner->sendNotificationEmail(
						Member::currentUser(), // sender
						$publisher, // recipient
						_t("{$this->owner->class}.EMAIL_SUBJECT_APPROVED_FOR_PUBLISHING"),
						_t("{$this->owner->class}.EMAIL_PARA_APPROVED_FOR_PUBLISHING"),
						$comment,
						'WorkflowGenericEmail'
					);
				}
			}
		}
		
		if (WorkflowRequest::should_send_alert(__CLASS__, 'approve', 'author')) {
			$this->owner->sendNotificationEmail(
				Member::currentUser(), // sender
				$author, // recipient
				_t("{$this->owner->class}.EMAIL_SUBJECT_APPROVED_FOR_PUBLISHING"),
				_t("{$this->owner->class}.EMAIL_PARA_APPROVED_FOR_PUBLISHING"),
				$comment,
				'WorkflowGenericEmail'
			);
		}
	}
	
	function notifyComment($comment) {
		// Comment recipients cover everyone except the person making the comment
		$commentRecipients = array();
		if (WorkflowRequest::should_send_alert(__CLASS__, 'comment', 'author')) {
			if(Member::currentUserID() != $this->owner->Author()->ID) $commentRecipients[] = $this->owner->Author();
		}
		
		if (WorkflowRequest::should_send_alert(__CLASS__, 'comment', 'publisher')) {
			$receivers = $this->owner->Page()->ApproverMembers();
			foreach($receivers as $receiver) $commentRecipients[] = $receiver;
		}

		foreach($commentRecipients as $recipient) {
			if(Member::currentUserID() != $receiver->ID) continue;
			$this->owner->sendNotificationEmail(
				Member::currentUser(), // sender
				$recipient, // recipient
				_t("{$this->owner->owner->class}.EMAIL_SUBJECT_COMMENT"),
				_t("{$this->class}.EMAIL_PARA_COMMENT"),
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
		$publishers = $this->owner->Page()->ApproverMembers();
		$author = $this->owner->Author();

		if (WorkflowRequest::should_send_alert(__CLASS__, 'request', 'publisher')) {
			foreach($publishers as $publisher){
				$this->owner->sendNotificationEmail(
					$author, // sender
					$publisher, // recipient
					_t("{$this->class}.EMAIL_SUBJECT_AWAITINGAPPROVAL"),
					_t("{$this->class}.EMAIL_PARA_AWAITINGAPPROVAL"),
					$comment,
					'WorkflowGenericEmail'
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
		
		if($this->owner->Status == 'Approved' && $this->owner->Page()->canPublish()) {
			$actions['cms_publish'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_ACTION", "Publish change");
			return $actions;
		} elseif($this->owner->Status == 'AwaitingApproval' && $this->owner->Page()->canApprove()) {
			$actions['cms_approve'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_APPROVE", "Approve");
			$actions['cms_requestedit'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_REQUESTEDIT", "Request edit");
			if (self::$allow_deny) $actions['cms_deny'] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_DENY","Deny");
		} else if($this->owner->Status == 'AwaitingEdit' && $this->owner->Page()->canEdit()) {
			// @todo this couples this class to its subclasses. :-(
			$requestAction = (get_class($this) == 'WorkflowDeletionRequest') ? 'cms_requestdeletefromlive' : 'cms_requestpublication';
			$actions[$requestAction] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_RESUBMIT", "Re-submit");
		}
		
		$actions['cms_comment'] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_COMMENT", "Comment");

		if ($this->owner->Page()->canEdit()) {
			$actions['cms_cancel'] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_CANCEL","Cancel");
		}

		return $actions;
	}
	
	public static function get_by_approver($class, $approver, $status = null) {
		// To ensure 2.3 and 2.4 compatibility
		$bt = defined('Database::USE_ANSI_SQL') ? "\"" : "`";

		if($status) $statusStr = "'".implode("','", $status)."'";

		$classes = (array)ClassInfo::subclassesFor($class);
		$classes[] = $class;
		$classesSQL = implode("','", $classes);
		
		// build filter
		
		// check for admin permission
		if (Permission::checkMember($approver, 'ADMIN') || Permission::checkMember($approver, 'IS_WORKFLOW_ADMIN')) {
			// Admins can approve/publish anything
			$filter = "{$bt}WorkflowRequest{$bt}.ClassName IN ('$classesSQL')";
		} else {
			$filter = "{$bt}WorkflowRequest_Approvers{$bt}.MemberID = {$approver->ID} 
				AND {$bt}WorkflowRequest{$bt}.ClassName IN ('$classesSQL')
			";
		}
		
		if($status) {
			$filter .= "AND {$bt}WorkflowRequest{$bt}.Status IN (" . $statusStr . ")";
		} 
		
		return DataObject::get(
			"SiteTree", 
			$filter, 
			"{$bt}SiteTree{$bt}.{$bt}LastEdited{$bt} DESC",
			"LEFT JOIN {$bt}WorkflowRequest{$bt} ON {$bt}WorkflowRequest{$bt}.PageID = {$bt}SiteTree{$bt}.ID " .
			"LEFT JOIN {$bt}WorkflowRequest_Approvers{$bt} ON {$bt}WorkflowRequest{$bt}.ID = {$bt}WorkflowRequest_Approvers{$bt}.WorkflowRequestID"
		);
	}
	
	public static function get_by_publisher($class, $publisher, $status = null) {
		// To ensure 2.3 and 2.4 compatibility
		$bt = defined('Database::USE_ANSI_SQL') ? "\"" : "`";

		if($status) $statusStr = "'".implode("','", $status)."'";

		$classes = (array)ClassInfo::subclassesFor($class);
		$classes[] = $class;
		$classesSQL = implode("','", $classes);
		
		// build filter
		$filter = "{$bt}WorkflowRequest{$bt}.ClassName IN ('$classesSQL') ";
		if($status) {
			$filter .= "AND {$bt}WorkflowRequest{$bt}.Status IN (" . $statusStr . ")";
		} 
		
		$doSet = new DataObjectSet();
		$objects = DataObject::get(
			"SiteTree", 
			$filter, 
			"{$bt}SiteTree{$bt}.{$bt}LastEdited{$bt} DESC",
			"LEFT JOIN {$bt}WorkflowRequest{$bt} ON {$bt}WorkflowRequest{$bt}.PageID = {$bt}SiteTree{$bt}.ID "
		);
		
		if ($objects) {
			foreach($objects as $do) {
				if ($do->canPublish($publisher)) {
					$doSet->push($do);
				}
			}
		}
		
		return $doSet;
		return WorkflowRequest::get_by_publisher($class, $publisher, $status);
	}
	
	public static function get_by_author($class, $author, $status = null) {
		return WorkflowRequest::get_by_author($class, $author, $status);
	}
	
	public static function get($class, $status = null) {
		return WorkflowRequest::get($class, $status);
	}
}