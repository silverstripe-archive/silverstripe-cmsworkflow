<?php
/**
 * A "workflow request" represents a full review process for one set of changes to a single page. 
 * Only one workflow request can be active for any given page; however, a page may have a number 
 * of historical, closed workflow requests.
 * 
 * The WorkflowRequest object shouldn't be directly edited.  Instead, you call "workflow step"
 * methods on the object, that will update the object appropriately.
 * 
 * To create or retrieve a WorkflowRequest object, call {@link SiteTreeCMSWorkflow::openOrNewWorkflowRequest()}
 * or {@link SiteTreeCMSWorkflow::openWorkflowRequest()} on the relevant {@link SiteTree} object.
 *
 * The following examples show how a workflow can be created.
 *
 * Request publication:
 * <code>
 * $wf = $page->openOrNewWorkflowRequest('WorkflowPublicationRequest')
 * $wf->request("Can you please publish this page");
 * </code>
 * 
 * Reject changes:
 * <code>
 * $wf = $page->openWorkflowRequest()
 * $wf->deny("It's not acceptable.  Please correct the spelling.");
 * </code>
 * 
 * Approve changes:
 * <code>
 * $wf = $page->openWorkflowRequest()
 * $wf->approve("Thanks, looks good now");
 * </code>
 *
 * Make the changes 'go live' changes:
 * <code>
 * $wf = $page->openWorkflowRequest()
 * $wf->action();
 * </code>
 * 
 * {@link WorkflowRequest::Changes()} will provide a list of the changes that the workflow has gone through,
 * suitable for presentation as a discussion thread attached to the page.
 * 
 * @package cmsworkflow
 */
class WorkflowRequest extends DataObject implements i18nEntityProvider {
	static $db = array(
		// @todo AwaitingReview
		'Status' => "Enum('AwaitingApproval,Approved,Scheduled,Completed,Denied,Cancelled,AwaitingEdit','AwaitingApproval')",
		'EmbargoDate' => 'SS_Datetime'
		// actioned is true/false whether the change has actually happened on live
	);
	
	static $has_one = array(
		'Author' => 'Member',
		'Approver' => 'Member',
		'Publisher' => 'Member',
		'Page' => 'SiteTree'
	);
	
	static $has_many = array(
		'Changes' => 'WorkflowRequestChange',
	);
	
	static $many_many = array(
		'Approvers' => 'Member'
	);
	
	static $allow_deny = true;
	
	/**
	 * Control who gets alerts for certain events
	 * data structure is fairly self-explanitory
	 * self::$alerts[CLASS][EVENT][USERROLE] = boolean
	 * Not all event/role combinations are neccessairily
	 * implemented by all ApprovalPaths.
	 */
	static $alerts = array();

	protected $memberIdsEmailed = array();

	/**
	 * Factory method setting up a new WorkflowRequest with associated
	 * state. Sets relations to publishers and authors, 
	 * 
	 * @param SiteTree $page
	 * @param Member $member The user requesting publication
	 * @param DataObjectSet $approvers Approvers assigned to this request.
	 * @return boolean|WorkflowPublicationRequest
	 */
	public static function create_for_page($page, $author = null, $approvers = null) {
		user_error('WorkflowRequest::create_for_page() - Abstract method, please implement in subclass', E_USER_ERROR);
	}
	
	/**
	 * Should we send an email to the following group under
	 * these circumstances. Default to false.
	 *
	 */
	public static function should_send_alert($class, $event, $group) {
		if (!isset(self::$alerts[$class])) return false;
		if (!isset(self::$alerts[$class][$event])) return false;
		if (!isset(self::$alerts[$class][$event][$group])) return false;
		return self::$alerts[$class][$event][$group];
	}
	
	public static function set_alert($class, $event, $group, $notify) {
		if (!isset(self::$alerts[$class])) self::$alerts[$class] = array();
		if (!isset(self::$alerts[$class][$event])) self::$alerts[$class][$event] = array();
		self::$alerts[$class][$event][$group] = $notify;
	}
	
	/**
	 * Approve this request, notify interested parties
	 * and close it. Used by {@link LeftAndMainCMSWorkflow}
	 * and {@link SiteTreeCMSWorkflow}.
	 * 
	 * @param Member $author
	 * @return boolean
	 */
	public function request($comment, $member = null) {
		if(!$member) $member = Member::currentUser();

		$this->Status = 'AwaitingApproval';
		$this->write();

		$this->addNewChange($comment, $this->Status, $member);
		$this->notifyAwaitingApproval($comment);
		
		return _t('SiteTreeCMSWorkflow.CHANGEREQUESTED','Requested this change. Emailed %s.');
	}
	
	/**
	 * Comment on a workflow item without changing the status
	 */
	public function comment($comment, $member = null, $notify = true) {
		if(!$member) $member = Member::currentUser();
		if(!$this->Page()->canEdit($member) && !$this->Page()->canPublish($member)) {
			return false;
		}
		$this->addNewChange($comment, null, $member);
		if($notify) $this->notifyComment($comment);

		return _t('SiteTreeCMSWorkflow.COMMENT_MESSAGE', 
			'Commented on this workflow request. Emailed %s.');
	}
	
	/**
	 * Request an edit to this page before it can be published.
	 * 
	 * @param Member $author
	 * @return boolean
	 */
	public function requestedit($comment, $member = null, $notify = true) {
		if(!$member) $member = Member::currentUser();
		if(!$this->Page()->canRequestEdit($member)) {
			return false;
		}
		
		// "publisher" in this sense means "deny-author"
		$this->PublisherID = $member->ID;
		$this->write();
		// open the request and notify interested parties
		$this->Status = 'AwaitingEdit';
		$this->write();

		$this->addNewChange($comment, $this->Status, $member);
		if($notify) $this->notifyAwaitingEdit($comment);
		
		return _t('SiteTreeCMSWorkflow.CHANGEREQUESTED','Requested this change. Emailed %s.');
	}
	
	/**
	 * Deny this request, notify interested parties
	 * and close it. Used by {@link LeftAndMainCMSWorkflow}
	 * and {@link SiteTreeCMSWorkflow}.
	 * 
	 * @param Member $author
	 * @return boolean
	 */
	public function deny($comment, $member = null, $notify = true) {
		if (!self::$allow_deny) return false;
		if(!$member) $member = Member::currentUser();
		if(!$this->Page()->canDenyRequests($member)) {
			return false;
		}
		
		// "publisher" in this sense means "deny-author"
		$this->ApproverID = $member->ID;
		$this->ActionerID = $member->ID;
		$this->Actioned = true;
		$this->write();
		
		// open the request and notify interested parties
		$this->Status = 'Denied';
		$this->write();

		// revert page to live (which might undo independent changes by other authors)
		if (Versioned::get_one_by_stage('SiteTree', 'Live', 'SiteTree_Live.ID = '.$this->Page()->ID)) {
			$this->Page()->doRevertToLive();
		}

		$this->addNewChange($comment, $this->Status, $member);
		if($notify) $this->notifyDenied($comment);
		
		return _t('SiteTreeCMSWorkflow.DENYPUBLICATION_MESSAGE','Denied workflow request, and reset content. Emailed %s');
	}
	
	/**
	 * Cancel this request, notify interested parties
	 * and close it. Used by {@link LeftAndMainCMSWorkflow}
	 * and {@link SiteTreeCMSWorkflow}.
	 * 
	 * @param Member $author
	 * @return boolean
	 */
	public function cancel($comment, $member = null, $notify = true) {
		if(!$member) $member = Member::currentUser();
		if(!$this->Page()->canEdit()) {
			return false;
		}
		
		// "publisher" in this sense means "deny-author"
		$this->ApproverID = $member->ID;
		$this->ActionerID = $member->ID;
		$this->Actioned = true;
		$this->write();
		
		// open the request and notify interested parties
		$this->Status = 'Cancelled';
		$this->write();

		$this->addNewChange($comment, $this->Status, $member);
		if($notify) $this->notifyCancelled($comment);
		
		return _t('SiteTreeCMSWorkflow.CANCELREQUEST_MESSAGE','Cancelled workflow request. Emailed %s');
	}
	
	/**
	 * Create a new {@link WorkflowRequestChange} with the current
	 * page status and versions, and link it to this object.
	 *
	 * @return WorkflowRequestChange
	 */
	public function addNewChange($comment, $status, $member) {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		
		$change = new WorkflowRequestChange();
		$change->AuthorID = $member->ID;
		$change->Status = $status;
		$change->Comment = $comment;
		
		$page = $this->Page();
		$draftPage = Versioned::get_one_by_stage('SiteTree', 'Draft', "{$bt}SiteTree{$bt}.{$bt}ID{$bt} = $page->ID", false, "Created DESC");
		// draftpage might not exist for pages "deleted from stage"
		if($draftPage) $change->PageDraftVersion = $draftPage->Version;
		$livePage = Versioned::get_one_by_stage('SiteTree', 'Live', "{$bt}SiteTree{$bt}.{$bt}ID{$bt} = $page->ID", false, "Created DESC");
		// livepage might not exist for pages which have never been published
		if($livePage) $change->PageLiveVersion = $livePage->Version;
		$change->write();
		$this->Changes()->add($change);
		
		return $change;
	}
	
	function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$diffLinkTitle = _t('SiteTreeCMSWorkflow.DIFFERENCESLINK', 'Show differences to live');
		
		$tf = $fields->dataFieldByName('Changes');
		$tf->setFieldList(array(
			'Created' => $this->fieldLabel('Created'), 
			'Author.Title' => $this->fieldLabel('Author'), 
			'Comment' => $this->fieldLabel('Comment'), 
			'StatusDescription' => $this->fieldLabel('Status'), 
			'DiffLinkToLastPublished' => _t('SiteTreeCMSWorkflow.DIFFERENCESTOLIVECOLUMN', 'Differences to live'),
			'DiffLinkContentToPrevious' => _t('SiteTreeCMSWorkflow.DIFFERENCESTHISCHANGECOLUMN', 'Differences in this change'),
		));
		$tf->setFieldFormatting(array(
			"DiffLinkToLastPublished" => '<a href=\"$value\" target=\"_blank\" class=\"externallink\">Show</a>',
			// "DiffLinkToPrevious" => '<a href=\"$value\" target=\"_blank\" class=\"externallink\">Show</a>'
		));
		$fields->replaceField(
			'Status',
			new ReadonlyField('StatusDescription', $this->fieldLabel('Status'), $this->StatusDescription)
		);
		
		return $fields;
	}
	
	function ApprovalDate() {
		$change = DataObject::get_one('WorkflowRequestChange', "WorkflowRequestID = $this->ID AND Status = 'AwaitingApproval'", "ID DESC");
		return $change ? $change->Created : null;
	}
	
	function getCMSDetailFields() {
		$fields = $this->getFrontEndFields();
		$fields->insertBefore(
			$titleField = new ReadonlyField(
				'RequestTitleField',
				$this->fieldLabel('Title'),
				$this->getTitle()
			),
			'Status'
		);
		$fields->push(
			$showDifferencesField = new ReadonlyField(
				'ShowDifferencesLink',
				false,
				sprintf(
					'<a href="%s">%s</a>', 
					$this->DiffLinkToLastPublished,
					_t('SiteTreeCMSWorkflow.DIFFERENCESTOLIVECOLUMN', 'Differences to live')
				)
			)
		);
		$showDifferencesField->dontEscape = true;
		$fields->replaceField(
			'Status',
			new ReadonlyField(
				'StatusDescription', 
				$this->fieldLabel('Status'), 
				$this->StatusDescription
			)
		);
		
		return $fields;
	}
	
	/**
	 * Return the field used for setting Embargo/Expiry date.
	 * returns false if the field cant be used in this context
	 */
	function EmbargoField() {
		return new PopupDateTimeField('EmbargoDate', 'Embargo Date', $this->EmbargoDate);
	}
	function ExpiryField() {
		return new PopupDateTimeField('ExpiryDate', 'Expiry Date', $this->ExpiryDate);
	}
	
	function getEmbargoDate() {
		return $this->getField('EmbargoDate') != '0000-00-00 00:00:00' && $this->getField('EmbargoDate') != null ? $this->getField('EmbargoDate') : null;
	}
	
	function getExpiryDate() {
		return $this->ExpiryDate();
	}
	
	function ExpiryDate() {
		return $this->Page()->getField('ExpiryDate') != '0000-00-00 00:00:00' && $this->Page()->getField('ExpiryDate') != null ? $this->Page()->getField('ExpiryDate') : null;
	}
	
	function WorkflowTimezone() {
		return date('T').', where is it currently '.date('r');
	}
	
	/**
	 * Return true/false whether we can currently change the PublishAt time
	 */
	function CanChangeEmbargoExpiry() {
		return $this->Status == 'AwaitingApproval';
	}
	
	/**
	 * Notify the author of a request once a page has been approved.
	 * Whether this means the reuqest has been actioned depends on
	 * the approval path.
	 */
	public function notifyApproved($comment) {
		$author = $this->Author();
		$subject = sprintf(
			_t("{$this->class}.EMAIL_SUBJECT_APPROVED"),
			$this->owner->Page()->Title
		);

		if (self::should_send_alert(__CLASS__, 'approve', 'author')) {
			$this->owner->sendNotificationEmail(
				Member::currentUser(), // sender
				$author, // recipient
				$comment,
				_t('WorkflowRequest.APPROVED_CHANGES', 'approved changes')
			);
		}
	}
	
	function notifyDenied($comment) {
		$publisher = Member::currentUser();
		$author = $this->Author();

		if (self::should_send_alert(__CLASS__, 'deny', 'author')) {
			$this->sendNotificationEmail(
				$publisher, // sender
				$author, // recipient
				$comment,
				_t('WorkflowRequest.DENIED_REQUEST', 'denied the request')
			);
		}
	}
	
	function notifyCancelled($comment) {
		$publisher = Member::currentUser();
		$author = $this->Author();

		if (self::should_send_alert(__CLASS__, 'cancel', 'author')) {
			$this->sendNotificationEmail(
				$publisher, // sender
				$author, // recipient
				$comment,
				_t('WorkflowRequest.CANCELLED_REQUEST', 'cancelled changes')
			);
		}
	}

	function notifyAwaitingEdit($comment) {
		$sender = Member::currentUser();
		$author = $this->Author();

		$this->sendNotificationEmail(
			$sender, // sender
			$author, // recipient
			$comment,
			_t('WorkflowRequest.REQUESTED_CHANGED', 'requested further changes')
		);
	}

	public function sendNotificationEmail($sender, $recipient, $comment, $requestedAction, $template = null) {
		$this->addMemberEmailed($recipient);

		if(!$template) {
			$template = 'WorkflowGenericEmail';
		}
		
		$subject = sprintf(_t('WorkflowRequest.EMAIL_SUBJECT', 'CMS Workflow: %s - Page: %s - Status: %s'), SiteConfig::current_site_config()->Title, $this->Page()->Title, self::get_status_description($this->Status));

		$email = new Email();
		$email->setTo($recipient->Email);
		$email->setFrom(($sender->Email) ? $sender->Email : Email::getAdminEmail());
		$email->setTemplate($template);
		$email->setSubject($subject);
		$email->populateTemplate(array(
			"PageCMSLink" => "admin/show/".$this->Page()->ID,
			"Recipient" => $recipient,
			"Sender" => $sender,
			"Page" => $this->Page(),
			"StageSiteLink"	=> $this->Page()->Link()."?stage=stage",
			"LiveSiteLink"	=> $this->Page()->Link()."?stage=live",
			"Workflow" => $this,
			"Comment" => $comment,
			'RequestedAction' => strtolower($requestedAction)
		));
		return $email->send();
	}

	/**
	 * Add a member to the 'i've emailed them' list
	 *
	 * @param Member $member 
	 */
	final public function addMemberEmailed(Member $member) {
		$this->memberIdsEmailed[] = (int)$member->ID;
	}
	
	/**
	 * Get a list of people emails this http request
	 *
	 * @return DataObjectSet
	 */
	final public function getMembersEmailed() {
		$doSet = new DataObjectSet();
		foreach(array_unique($this->memberIdsEmailed) as $id) {
			$doSet->push(DataObject::get_by_id('Member', $id));
		}
		return $doSet;
	}
	
	/**
	 * Clear the list of people emailed this http request
	 *
	 * @return void
	 */
	final public function clearMembersEmailed() {
		$this->memberIdsEmailed = array();
	}
		
	/**
	 * Returns a {@link DataDifferencer} object representing the changes. Has
	 * some nasty logic to make it so that only changes that are made through
	 * fields that are exposed by the CMS are tracked.
	 */
	public function Diff() {
		$diff = new DataDifferencer($this->fromRecord(), $this->toRecord());
		
		// $dataObjectFields = array_flip($this->fromRecord()->record);
		// asort($dataObjectFields);
		// $cmsFields = array();
		// 
		// $cms = $this->getCMSFields();
		// var_dump($cms);
		// 
		// foreach($this->getCMSFields() as $tab1) {
		// 	if ($tab1 instanceof TabSet) {
		// 		echo $tab1->Name().get_class($tab1).' 1<br/>';
		// 		foreach($tab1->Tabs() as $tab2) {
		// 			echo $tab2->Name().get_class($tab2).' 2<br/>';
		// 			if ($tab2 instanceof TabSet) {
		// 				foreach($tab2->Tabs() as $tab3) {
		// 					echo $tab3->Name().get_class($tab3).' 3<br/>';
		// 					foreach($tab3->Fields() as $field) $cmsFields[] = $field->Name();
		// 				}
		// 			} else {
		// 				foreach($tab2->Fields() as $field) $cmsFields[] = $field->Name();
		// 			}
		// 		}
		// 	} else {
		// 		foreach($tab1->Fields() as $field) $cmsFields[] = $field->Name();
		// 	}
		// }
		// 
		// print_r($cmsFields);
		// 
		// $diff->ignoreFields(array_diff($cmsFields, $dataObjectFields));
		
		return $diff;
	}
	
	/**
	 * Returns the old record that will be replaced by this publication.
	 */
	public function fromRecord() {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		return Versioned::get_one_by_stage('SiteTree', 'Live', "{$bt}SiteTree_Live{$bt}.ID = {$this->PageID}", true, "Created DESC");
	}
	
	/**
	 * Returns the new record for which publication is being requested.
	 */
	public function toRecord() {
		return $this->Page();
	}
	
	/**
	 * Is the workflow request still pending.
	 * Important for creation of new workflow requests
	 * as there should be only one open request
	 * per page at any given point in time.
	 * 
	 * @return boolean
	 */
	public function isOpen() {
		return (!in_array($this->Status,array('Approved','Denied')));
	}
	
	/**
	 * Returns a CMS link to see differences made in the request
	 * 
	 * @return string URL
	 */
	protected function getDiffLinkToLastPublished() {		
		// Get the completed request change and ask it
		$completedChange = DataObject::get_one('WorkflowRequestChange', "WorkflowRequestID = {$this->ID} AND Status = 'Completed'");
		if (!$completedChange) return false;
		return $completedChange->getDiffLinkToLastPublished();
	}
	
	/**
	 * Determines if a request can be created by an author for a specific page.
	 * Add custom authentication checks by subclassing this method.
	 * 
	 * @param Member $member
	 * @param SiteTree $page
	 * @return boolean
	 */
	public static function can_create($member = NULL, $page) {
		if(!$member && $member !== FALSE) {
			$member = Member::currentUser();
		}

		return $page->canEdit($member);
	}
	
	/**
	 * Get all publication requests by a specific author
	 * 
	 * @param Member $author
	 * @return DataObjectSet
	 */
	public static function get_by_author($class, $author, $status = null) {
		// To ensure 2.3 and 2.4 compatibility
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		
		if($status) $statusStr = "'".implode("','", $status)."'";

		$classes = (array)ClassInfo::subclassesFor($class);
		$classes[] = $class;
		$classesSQL = implode("','", $classes);
		
		// build filter
		$filter = "{$bt}Member{$bt}.ID = {$author->ID}  
			AND {$bt}WorkflowRequest{$bt}.ClassName IN ('$classesSQL')
		";
		if($status) {
			$filter .= "AND {$bt}WorkflowRequest{$bt}.Status IN (" . $statusStr . ")";
		}
		
		return DataObject::get(
			"SiteTree", 
			$filter, 
			"{$bt}SiteTree{$bt}.{$bt}LastEdited{$bt} DESC",
			"LEFT JOIN {$bt}WorkflowRequest{$bt} ON {$bt}WorkflowRequest{$bt}.PageID = {$bt}SiteTree{$bt}.ID " .
			"LEFT JOIN {$bt}Member{$bt} ON {$bt}Member{$bt}.ID = {$bt}WorkflowRequest{$bt}.AuthorID"
		);
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
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";

		if($status) $statusStr = "'".implode("','", $status)."'";

		$classes = (array)ClassInfo::subclassesFor($class);
		$classes[] = $class;
		$classesSQL = implode("','", $classes);
		
		// build filter
		$filter = "{$bt}WorkflowRequest_Publishers{$bt}.MemberID = {$publisher->ID} 
			AND {$bt}WorkflowRequest{$bt}.ClassName IN ('$classesSQL')
		";
		if($status) {
			$filter .= "AND {$bt}WorkflowRequest{$bt}.Status IN (" . $statusStr . ")";
		} 
		
		return DataObject::get(
			"SiteTree", 
			$filter, 
			"{$bt}SiteTree{$bt}.{$bt}LastEdited{$bt} DESC",
			"LEFT JOIN {$bt}WorkflowRequest{$bt} ON {$bt}WorkflowRequest{$bt}.PageID = {$bt}SiteTree{$bt}.ID " .
			"LEFT JOIN {$bt}WorkflowRequest_Publishers{$bt} ON {$bt}WorkflowRequest{$bt}.ID = {$bt}WorkflowRequest_Publishers{$bt}.WorkflowRequestID"
		);
	}
	
	/**
	 * Get all publication requests assigned to a specific approver
	 * 
	 * @param string $class WorkflowRequest subclass
	 * @param Member $approver
	 * @param array $status One or more stati from the $Status property
	 * @return DataObjectSet
	 */
	public static function get_by_approver($class, $approver, $status = null) {
		// To ensure 2.3 and 2.4 compatibility
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";

		if($status) $statusStr = "'".implode("','", $status)."'";

		$classes = (array)ClassInfo::subclassesFor($class);
		$classes[] = $class;
		$classesSQL = implode("','", $classes);
		
		// build filter
		$filter = '';
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
	
	/**
	 * Get publication requests from all users
	 * @param string $class WorkflowRequest subclass
	 * @param array $status One or more stati from the $Status property
	 * @return DataObjectSet
	 */
	public static function get($class, $status = null) {
		// To ensure 2.3 and 2.4 compatibility
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";

		if($status) $statusStr = implode(',', $status);

		$classes = (array)ClassInfo::subclassesFor($class);
		$classes[] = $class;
		$classesSQL = implode("','", $classes);
		
		// build filter
		$filter = "{$bt}WorkflowRequest{$bt}.ClassName IN ('$classesSQL')";
		if($status) {
			$filter .= "AND {$bt}WorkflowRequest{$bt}.Status IN ('" . Convert::raw2sql($statusStr) . "')";
		} 
		
		return DataObject::get(
			"SiteTree", 
			$filter, 
			"{$bt}SiteTree{$bt}.{$bt}LastEdited{$bt} DESC",
			"LEFT JOIN {$bt}WorkflowRequest{$bt} ON {$bt}WorkflowRequest{$bt}.PageID = {$bt}SiteTree{$bt}.ID"
		);
	}
	
	/**
	 * @return string
	 */
	public function getTitle() {
		$title = _t("{$this->class}.TITLE");
		if(!$title) $title = _t('WorkflowRequest.TITLE');
		
		return $title;
	}
	
	/**
	 * @return string Translated $Status property
	 */
	public function getStatusDescription() {
		return self::get_status_description($this->Status);
	}
	
	public static function get_status_description($status) {
		switch($status) {
			case 'Open':
				return _t('SiteTreeCMSWorkflow.STATUS_OPEN', 'Open');
			case 'Approved':
				return _t('SiteTreeCMSWorkflow.STATUS_APPROVED', 'Approved');
			case 'Scheduled':
				return _t('SiteTreeCMSWorkflow.STATUS_SCHEDULED', 'Scheduled for Publishing');
			case 'Completed':
				return _t('SiteTreeCMSWorkflow.STATUS_COMPLETED', 'Completed');
			case 'AwaitingApproval':
				return _t('SiteTreeCMSWorkflow.STATUS_AWAITINGAPPROVAL', 'Awaiting Approval');
			case 'AwaitingEdit':
				return _t('SiteTreeCMSWorkflow.STATUS_AWAITINGEDIT', 'Awaiting Edit');
			case 'Denied':
				return _t('SiteTreeCMSWorkflow.STATUS_DENIED', 'Denied');
			case 'Cancelled':
				return _t('SiteTreeCMSWorkflow.STATUS_CANCELLED', 'Cancelled');
			default:
				return _t('SiteTreeCMSWorkflow.STATUS_'.strtoupper($status), $status);
		}
	}
	
	function fieldLabels() {
		$labels = parent::fieldLabels();
		
		$labels['Status'] = _t('SiteTreeCMSWorkflow.FIELDLABEL_STATUS', "Status");
		$labels['Author'] = _t('SiteTreeCMSWorkflow.FIELDLABEL_AUTHOR', "Author");
		$labels['Publisher'] = _t('SiteTreeCMSWorkflow.FIELDLABEL_PUBLISHER', "Publisher");
		$labels['Page'] = _t('SiteTreeCMSWorkflow.FIELDLABEL_PAGE', "Page");
		$labels['Publishers'] = _t('SiteTreeCMSWorkflow.FIELDLABEL_PUBLISHERS', "Publishers");
		
		return $labels;
	}
	
	function provideI18nEntities() {
		$entities = array();
		$entities['WorkflowRequest.EMAIL_SUBJECT_GENERIC'] = array(
			"The workflow status of the \"%s\" page has changed",
			PR_MEDIUM,
			'Email subject with page title'
		);
		$entities['WorkflowRequest.TITLE'] = array(
			"Workflow Request",
			PR_MEDIUM,
			'Title for this request, shown e.g. in the workflow status overview for a page'
		);
		
		return $entities;
	}
	
	public function setSchedule() {
		if ($this->EmbargoDate) {
			$this->Status = 'Scheduled';
			$this->write();
		}
	}
}
?>