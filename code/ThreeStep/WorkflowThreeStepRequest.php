<?php

/**
 * ThreeStep, where an item is not actioned immediately.
 *
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class WorkflowThreeStepRequest extends WorkflowRequestDecorator {
	function approve($comment, $member = null, $notify = true) {
		if(!$member) $member = Member::currentUser();
		if(!$this->owner->Page()->canApprove($member)) {
			return false;
		}
	
		if ($this->owner->ClassName == 'WorkflowDeletionRequest') {
			if (isset($_REQUEST['DeletionScheduling']) 
				&& $_REQUEST['DeletionScheduling'] == 'scheduled'
				&& $_REQUEST['ExpiryDate']['Date']) {
				// Update SiteTree_Live directly, rather than doing a publish
				// Because otherwise, unauthorized edits could be pushed live.
				
				list($day, $month, $year) = explode('/', $_REQUEST['ExpiryDate']['Date']);
				$expiryTimestamp = Convert::raw2sql(date('Y-m-d H:i:s', strtotime("$year-$month-$day {$_REQUEST['ExpiryDate']['Time']}")));
				$pageID = $this->owner->Page()->ID;
				
				if ($expiryTimestamp) {
					DB::query("UPDATE SiteTree_Live SET ExpiryDate = '$expiryTimestamp' WHERE ID = $pageID");
				}

				$this->owner->ApproverID = $member->ID;
				$this->owner->Status = 'Completed';
				$this->owner->write();
				
				$this->owner->addNewChange($comment, $this->owner->Status, $member);
				if($notify) $this->notifyApproved($comment);
				return _t('WorkflowDeletionRequest.SETEXPIRY','Set Expiry date. Emailed %s');
			}
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
		
		if ($notify) $this->notifyPublished($comment);
		
		return $this->owner->publish($comment, $member, $notify);
	}
	
	function saveAndPublish($comment, $member = null, $notify = true) {
		$this->approve($comment, $member, $notify);
		$this->publish($comment, $member, $notify);
		return _t('WorkflowThreeStepRequest.PUBLISHMESSAGE','Published changes to live version. Emailed %s.');
	}
	
	function notifyApproved($comment) {
		$author = $this->owner->Author();
		
		if (WorkflowRequest::should_send_alert($this->owner->class, 'approve', 'publisher')) {
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
		
		if (WorkflowRequest::should_send_alert($this->owner->class, 'approve', 'author')) {
			$this->owner->sendNotificationEmail(
				Member::currentUser(), // sender
				$author, // recipient
				$comment,
				_t('WorkflowRequest.APPROVED_CHANGES', 'approved changes')
			);
		}
	}
	
	function notifyPublished($comment) {
		$author = $this->owner->Author();

		if (WorkflowRequest::should_send_alert($this->owner->class, 'publish', 'publisher')) {
			$publishers = $this->owner->Page()->PublisherMembers();
			foreach($publishers as $publisher){
				// Notify publishers other than the one who is logged in 
				if(Member::currentUserID() != $publisher->ID) {
					$this->owner->sendNotificationEmail(
						Member::currentUser(), // sender
						$publisher, // recipient
						$comment,
						_t('WorkflowRequest.PUBLISHED_CHANGES', 'published changes')
					);
				}
			}
		}
		
		if (WorkflowRequest::should_send_alert($this->owner->class, 'publish', 'author')) {
			$this->owner->sendNotificationEmail(
				Member::currentUser(), // sender
				$author, // recipient
				$comment,
				_t('WorkflowRequest.PUBLISHED_CHANGES', 'published changes')
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
			$receivers = $this->owner->Page()->ApproverMembers();
			foreach($receivers as $receiver) $commentRecipients[] = $receiver;
		}

		foreach($commentRecipients as $recipient) {
			if(Member::currentUserID() != $receiver->ID) continue;
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
		$publishers = $this->owner->Page()->ApproverMembers();
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
		
		if($this->owner->Status == 'Approved' && $this->owner->Page()->canPublish()) {
			$actions['cms_publish'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_ACTION", "Publish change");
			if (get_class($this->owner) != 'WorkflowDeletionRequest') $actions['cms_requestedit'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_REQUESTEDIT", "Request edit");
		} elseif($this->owner->Status == 'Scheduled' && $this->owner->Page()->canApprove()) {
			$actions['cms_requestedit'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_REQUESTEDIT", "Request edit");
		} elseif($this->owner->Status == 'Scheduled' && $this->owner->Page()->canApprove()) {
			$actions['cms_requestedit'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_REQUESTEDIT", "Request edit");
		} elseif($this->owner->Status == 'AwaitingApproval' && $this->owner->Page()->canApprove()) {
			$actions['cms_approve'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_APPROVE", "Approve");
			if (get_class($this->owner) != 'WorkflowDeletionRequest') $actions['cms_requestedit'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_REQUESTEDIT", "Request edit");
			if (WorkflowRequest::$allow_deny) $actions['cms_deny'] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_DENY","Deny and revert");
		} else if($this->owner->Status == 'AwaitingEdit' && $this->owner->Page()->canEdit()) {
			// @todo this couples this class to its subclasses. :-(
			$requestAction = (get_class($this) == 'WorkflowDeletionRequest') ? 'cms_requestdeletefromlive' : 'cms_requestpublication';
			$actions[$requestAction] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_RESUBMIT", "Re-submit");
		}
		
		$actions['cms_comment'] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_COMMENT", "Comment");

		if ($this->owner->Page()->canEdit()) {
			$actions['cms_cancel'] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_CANCEL","Cancel workflow");
		}

		return $actions;
	}
	
	public static function get_by_approver($class, $approver, $status = null) {
		// To ensure 2.3 and 2.4 compatibility
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";

		if($status) $statusStr = "'".implode("','", $status)."'";

		$classes = (array)ClassInfo::subclassesFor($class);
		$classes[] = $class;
		$classesSQL = implode("','", $classes);
		
		// build filter
		
		// check for admin permission
		if (Permission::checkMember($approver, 'ADMIN') || Permission::checkMember($approver, 'IS_WORKFLOW_ADMIN')) {
			// Admins can approve/publish anything
			$filter = "{$bt}WorkflowRequest{$bt}.{$bt}ClassName{$bt} IN ('$classesSQL')";
		} else {
			$filter = "{$bt}WorkflowRequest_Approvers{$bt}.{$bt}MemberID{$bt} = {$approver->ID} 
				AND {$bt}WorkflowRequest{$bt}.{$bt}ClassName{$bt} IN ('$classesSQL')
			";
		}
		
		if($status) {
			$filter .= "AND {$bt}WorkflowRequest{$bt}.{$bt}Status{$bt} IN (" . $statusStr . ")";
		} 

		$onDraft = Versioned::get_by_stage(
			"SiteTree",
			"Stage",
			$filter, 
			"{$bt}SiteTree{$bt}.{$bt}LastEdited{$bt} DESC",
			"LEFT JOIN {$bt}WorkflowRequest{$bt} ON {$bt}WorkflowRequest{$bt}.{$bt}PageID{$bt} = {$bt}SiteTree{$bt}.ID " .
			"LEFT JOIN {$bt}WorkflowRequest_Approvers{$bt} ON {$bt}WorkflowRequest{$bt}.{$bt}ID{$bt} = {$bt}WorkflowRequest_Approvers{$bt}.{$bt}WorkflowRequestID{$bt}"
		);
		
		$onLive = Versioned::get_by_stage(
			"SiteTree",
			"Live",
			$filter, 
			"{$bt}SiteTree_Live{$bt}.{$bt}LastEdited{$bt} DESC",
			"LEFT JOIN {$bt}WorkflowRequest{$bt} ON {$bt}WorkflowRequest{$bt}.{$bt}PageID{$bt} = {$bt}SiteTree_Live{$bt}.{$bt}ID{$bt} " .
			"LEFT JOIN {$bt}WorkflowRequest_Approvers{$bt} ON {$bt}WorkflowRequest{$bt}.{$bt}ID{$bt} = {$bt}WorkflowRequest_Approvers{$bt}.{$bt}WorkflowRequestID{$bt}"
		);

		$return = new DataObjectSet();
		$return->merge($onDraft);
		$return->merge($onLive);
		$return->removeDuplicates();
		return $return;
	}
	
	public static function get_by_publisher($class, $publisher, $status = null) {
		// To ensure 2.3 and 2.4 compatibility
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";

		if($status) $statusStr = "'".implode("','", $status)."'";

		$classes = (array)ClassInfo::subclassesFor($class);
		$classes[] = $class;
		$classesSQL = implode("','", $classes);
		
		// build filter
		$filter = "{$bt}WorkflowRequest{$bt}.{$bt}ClassName{$bt} IN ('$classesSQL') ";
		if($status) {
			$filter .= "AND {$bt}WorkflowRequest{$bt}.{$bt}Status{$bt} IN (" . $statusStr . ")";
		} 
		
		$onDraft = Versioned::get_by_stage(
			"SiteTree",
			"Stage",
			$filter, 
			"{$bt}SiteTree{$bt}.{$bt}LastEdited{$bt} DESC",
			"LEFT JOIN {$bt}WorkflowRequest{$bt} ON {$bt}WorkflowRequest{$bt}.{$bt}PageID{$bt} = {$bt}SiteTree{$bt}.{$bt}ID{$bt} "
		);
		
		$onLive = Versioned::get_by_stage(
			"SiteTree",
			"Live",
			$filter, 
			"{$bt}SiteTree_Live{$bt}.{$bt}LastEdited{$bt} DESC",
			"LEFT JOIN {$bt}WorkflowRequest{$bt} ON {$bt}WorkflowRequest{$bt}.{$bt}PageID{$bt} = {$bt}SiteTree_Live{$bt}.{$bt}ID{$bt} "
		);

		$objects = new DataObjectSet();
		$return = new DataObjectSet();
		$objects->merge($onDraft);
		$objects->merge($onLive);
		$objects->removeDuplicates();
		
		if ($objects) {
			foreach($objects as $do) {
				if ($do->canPublish($publisher)) {
					$return->push($do);
				}
			}
		}
		
		return $return;
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
				'approve' => array(
					'author' => true,
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
}