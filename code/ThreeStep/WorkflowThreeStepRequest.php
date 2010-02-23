<?php

/**
 * ThreeStep, where an item is not actioned immediately.
 *
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class WorkflowThreeStepRequest extends WorkflowRequestDecorator {
	
	static $default_alerts = array(
		'WorkflowPublicationRequest' => array(
			'request' => array(
				'author' => false,
				'publisher' => false,
				'approver' => true
			),
			'approve' => array(
				'author' => true,
				'publisher' => true,
				'approver' => false
			),
			'publish' => array(
				'author' => true,
				'publisher' => false,
				'approver' => true
			),
			'deny' => array(
				'author' => true,
				'publisher' => true,
				'approver' => true
			),
			'cancel' => array(
				'author' => true,
				'publisher' => true,
				'approver' => true
			),
			'comment' => array(
				'author' => false,
				'publisher' => false,
				'approver' => false
			)
		),
		'WorkflowDeletionRequest' => array(
			'request' => array(
				'author' => false,
				'publisher' => false,
				'approver' => true
			),
			'approve' => array(
				'author' => true,
				'publisher' => true,
				'approver' => false
			),
			'publish' => array(
				'author' => true,
				'publisher' => false,
				'approver' => true
			),
			'deny' => array(
				'author' => true,
				'publisher' => true,
				'approver' => true
			),
			'cancel' => array(
				'author' => true,
				'publisher' => true,
				'approver' => true
			),
			'comment' => array(
				'author' => false,
				'publisher' => false,
				'approver' => false
			)
		)
	);
	
	function approve($comment, $member = null, $notify = true) {
		if(!$member) $member = Member::currentUser();
		if(!$this->owner->Page()->canApprove($member)) {
			return false;
		}
	
		// Todo - remove UI<->model coupling
		// See SiteTreeFutureStateTest
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
					$pageIDs = array($pageID);
					
					// Expire virtual pages linked to this also
					$pageIDs = array_merge($pageIDs, DB::query("SELECT \"ID\" FROM \"VirtualPage_Live\" 
						WHERE \"CopyContentFromID\" = $pageID")->column());
					
					DB::query("UPDATE \"SiteTree_Live\" SET \"ExpiryDate\" = '$expiryTimestamp' 
						WHERE \"ID\" IN (" . implode(", ", $pageIDs) . ")");
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

		$this->owner->addNewChange($comment, 'Approved', $member);
		$this->owner->setSchedule();

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
		
		if($notify) $this->notifyPublished($comment);

		$this->owner->addNewChange($comment, 'Published', $member);
		
		return $this->owner->publish($comment, $member, $notify);
	}
	
	function saveAndPublish($comment, $member = null, $notify = true) {
		$this->approve($comment, $member, $notify);
		$this->publish($comment, $member, $notify);
		return _t('WorkflowThreeStepRequest.PUBLISHMESSAGE','Published changes to live version. Emailed %s.');
	}
	
	function notifyApproved($comment) {
		$emailsToSend = array();
		$userWhoApproved = Member::currentUser();
		
		if (WorkflowRequest::should_send_alert(get_class($this->owner), 'approve', 'publisher')) {
			$publishers = $this->owner->Page()->PublisherMembers();
			foreach($publishers as $publisher) $emailsToSend[] = array($userWhoApproved, $publisher);
		}
		if (WorkflowRequest::should_send_alert(get_class($this->owner), 'approve', 'approver')) {
			$approvers = $this->owner->Page()->ApproverMembers();
			foreach($approvers as $approver) $emailsToSend[] = array($userWhoApproved, $approver);
		}
		if (WorkflowRequest::should_send_alert(get_class($this->owner), 'approve', 'author')) {
			$emailsToSend[] = array($userWhoApproved, $this->owner->Author());
		}
		
		if (count($emailsToSend)) {
			foreach($emailsToSend as $email) {
				if ($email[1]->ID == Member::currentUserID()) continue;
				$this->owner->sendNotificationEmail(
					$email[0], // sender
					$email[1], // recipient
					$comment,
					'approved changes'
				);
			}
		}
	}
	
	function notifyPublished($comment) {
		$emailsToSend = array();
		$userWhoPublished = Member::currentUser();
		
		if (WorkflowRequest::should_send_alert(get_class($this->owner), 'publish', 'publisher')) {
			$publishers = $this->owner->Page()->PublisherMembers();
			foreach($publishers as $publisher) $emailsToSend[] = array($userWhoPublished, $publisher);
		}
		if (WorkflowRequest::should_send_alert(get_class($this->owner), 'publish', 'approver')) {
			$approvers = $this->owner->Page()->ApproverMembers();
			foreach($approvers as $approver) $emailsToSend[] = array($userWhoPublished, $approver);
		}
		if (WorkflowRequest::should_send_alert(get_class($this->owner), 'publish', 'author')) {
			$emailsToSend[] = array($userWhoPublished, $this->owner->Author());
		}
		
		if (count($emailsToSend)) {
			foreach($emailsToSend as $email) {
				if ($email[1]->ID == Member::currentUserID()) continue;
				$this->owner->sendNotificationEmail(
					$email[0], // sender
					$email[1], // recipient
					$comment,
					'published changes'
				);
			}
		}
	}
	
	function notifyComment($comment) {
		$commentor = Member::currentUser();
		$emailsToSend = array();
		
		if (WorkflowRequest::should_send_alert(get_class($this->owner), 'comment', 'publisher')) {
			$publishers = $this->owner->Page()->PublisherMembers();
			foreach($publishers as $publisher) $emailsToSend[] = array($commentor, $publisher);
		}
		if (WorkflowRequest::should_send_alert(get_class($this->owner), 'comment', 'approver')) {
			$approvers = $this->owner->Page()->ApproverMembers();
			foreach($approvers as $approver) $emailsToSend[] = array($commentor, $approver);
		}
		if (WorkflowRequest::should_send_alert(get_class($this->owner), 'comment', 'author')) {
			$emailsToSend[] = array($commentor, $this->owner->Author());
		}
		
		if (count($emailsToSend)) {
			foreach($emailsToSend as $email) {
				if ($email[1]->ID == Member::currentUserID()) continue;
				$this->owner->sendNotificationEmail(
					$email[0], // sender
					$email[1], // recipient
					$comment,
					'commented'
				);
			}
		}
	}
	
	/**
	 * Notify any publishers assigned to this page when a new request
	 * is lodged.
	 */
	public function notifyAwaitingApproval($comment) {
		
		$author = $this->owner->Author();
		$emailsToSend = array();
		
		if (WorkflowRequest::should_send_alert(get_class($this->owner), 'request', 'publisher')) {
			$publishers = $this->owner->Page()->PublisherMembers();
			foreach($publishers as $publisher) $emailsToSend[] = array($author, $publisher);
		}
		if (WorkflowRequest::should_send_alert(get_class($this->owner), 'request', 'approver')) {
			$approvers = $this->owner->Page()->ApproverMembers();
			foreach($approvers as $approver) $emailsToSend[] = array($author, $approver);
		}
		if (WorkflowRequest::should_send_alert(get_class($this->owner), 'request', 'author')) {
			$emailsToSend[] = array($author, $author);
		}
		
		if (count($emailsToSend)) {
			foreach($emailsToSend as $email) {
				$this->owner->sendNotificationEmail(
					$email[0], // sender
					$email[1], // recipient
					$comment,
					'requested approval'
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
			$actions['cms_publish'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_ACTION", "Publish changes");
			if (get_class($this->owner) != 'WorkflowDeletionRequest') $actions['cms_requestedit'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_REQUESTEDIT", "Request edit");
		} elseif($this->owner->Status == 'Scheduled' && $this->owner->Page()->canApprove()) {
			$actions['cms_requestedit'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_REQUESTEDIT", "Request edit");
		} elseif($this->owner->Status == 'AwaitingApproval' && $this->owner->Page()->canApprove()) {
			$actions['cms_approve'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_APPROVE", "Approve");
			if (get_class($this->owner) != 'WorkflowDeletionRequest') $actions['cms_requestedit'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_REQUESTEDIT", "Request edit");
			if (WorkflowRequest::$allow_deny) $actions['cms_deny'] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_DENY","Deny");
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
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";

		if($status) $statusStr = "'".implode("','", $status)."'";

		$classes = (array)ClassInfo::subclassesFor($class);
		$classes[] = $class;
		$classesSQL = implode("','", $classes);
		
		$filter = "{$bt}WorkflowRequest{$bt}.{$bt}ClassName{$bt} IN ('$classesSQL')";

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
		
		$canApprove = SiteTree::batch_permission_check($return->column('ID'), $approver->ID, 'CanApproveType', 'SiteTree_ApproverGroups', 'canApprove');		
		foreach($return as $page) {
			if (!isset($canApprove[$page->ID]) || !$canApprove[$page->ID]) {
				$return->remove($page);
			}
		}
		
		return $return;
	}
	
	public static function get_by_publisher($class, $publisher, $status = null) {
		// To ensure 2.3 and 2.4 compatibility
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";

		if($status) $statusStr = "'".implode("','", $status)."'";

		$classes = (array)ClassInfo::subclassesFor($class);
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

		$return = new DataObjectSet();
		$return->merge($onDraft);
		$return->merge($onLive);
		$return->removeDuplicates();
		
		$canPublish = SiteTree::batch_permission_check($return->column('ID'), $publisher->ID, 'CanPublishType', 'SiteTree_PublisherGroups', 'canPublish');		
		foreach($return as $page) {
			if (!isset($canPublish[$page->ID]) || !$canPublish[$page->ID]) {
				$return->remove($page);
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
}