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
		
		$publishers = $this->owner->Page()->PublisherMembers();
		foreach($publishers as $publisher){
			// Notify publishers other than the one who is logged in 
			if(Member::currentUserID() != $publisher->ID) {
				$this->owner->sendNotificationEmail(
					Member::currentUser(), // sender
					$publisher, // recipient
					$comment,
					'approved'
				);
			}
		}

		$this->owner->sendNotificationEmail(
			Member::currentUser(), // sender
			$author, // recipient
			$comment,
			'approved'
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
				$comment,
				'comment'
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
				$comment,
				'awaiting approval'
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
			if (get_class($this->owner) != 'WorkflowDeletionRequest') $actions['cms_requestedit'] = _t("SiteTreeCMSWorkflow.WORKFLOWACTION_REQUESTEDIT", "Request edit");
			if (WorkflowRequest::$allow_deny) $actions['cms_deny'] = _t("SiteTreeCMSWorkflow.WORKFLOW_ACTION_DENY","Deny");
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
	
	/**
	 * Get all publication requests assigned to a specific publisher
	 * 
	 * @param string $class WorkflowRequest subclass
	 * @param Member $publisher
	 * @param array $status One or more stati from the $Status property
	 * @return DataObjectSet
	 */
	public static function get_by_publisher($class, $publisher, $status = null) {
		// To ensure 2.3 and 2.4 compatibility
		$bt = defined('Database::USE_ANSI_SQL') ? "\"" : "`";

		if($status) $statusStr = "'".implode("','", $status)."'";

		$classes = (array)ClassInfo::subclassesFor($class);
		$classes[] = $class;
		$classesSQL = implode("','", $classes);
		
		// build filter
		$filter = "{$bt}WorkflowRequest{$bt}.{$bt}ClassName{$bt} IN ('$classesSQL')
		";
		if($status) {
			$filter .= "AND {$bt}WorkflowRequest{$bt}.{$bt}Status{$bt} IN (" . $statusStr . ")";
		} 
		
		$return = DataObject::get(
			"SiteTree", 
			$filter, 
			"{$bt}SiteTree{$bt}.{$bt}LastEdited{$bt} DESC",
			"LEFT JOIN {$bt}WorkflowRequest{$bt} ON {$bt}WorkflowRequest{$bt}.{$bt}PageID{$bt} = {$bt}SiteTree{$bt}.{$bt}ID{$bt} " .
			"LEFT JOIN {$bt}WorkflowRequest_Publishers{$bt} ON {$bt}WorkflowRequest{$bt}.{$bt}ID{$bt} = {$bt}WorkflowRequest_Publishers{$bt}.{$bt}WorkflowRequestID{$bt}"
		);
		
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