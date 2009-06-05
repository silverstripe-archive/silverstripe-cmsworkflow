<?php
/**
 * A "deletion request" is created when an author without
 * rights to delete a page from the live site changes a page in draft mode, and explicitly
 * requests it to be reviewed for deletion.
 * Each request can have one or more "Publishers" which
 * should have permissions to delete the specific page.
 * 
 * @package cmsworkflow
 */
class WorkflowDeletionRequest extends WorkflowRequest implements i18nEntityProvider {
	
	/**
	 * @param string $emailtemplate_creation
	 */
	protected static $emailtemplate_awaitingapproval = 'DeletionAwaitingApprovalEmail';
	
	/**
	 * @param string $emailtemplate_approved
	 */
	protected static $emailtemplate_approved = 'WorkflowGenericEmail';
	
	/**
	 * @param string $emailtemplate_denied
	 */
	protected static $emailtemplate_denied = 'DeletionDeniedEmail';
	
	public static function create_for_page($page, $author = null, $publishers = null) {
		if(!$author && $author !== FALSE) $author = Member::currentUser();
		
		if(!WorkflowDeletionRequest::can_create($author, $page)) {
			return null;
		}
		
		// take all members from the PublisherGroups relation on this record as a default
		if(!$publishers) $publishers = $page->PublisherMembers();
		
		// if no publishers are set, the request will end up nowhere
		if(!$publishers->Count()) {
			return null;
		}
		
		// get or create a publication request
		$request = $page->OpenWorkflowRequest();
		if(!$request || !$request->ID) {
			$request = new WorkflowDeletionRequest();
			$request->PageID = $page->ID;
			$request->write();
		}
		
		// @todo Check for correct workflow class (a "publication" request might be overwritten with a "deletion" request)
		
		// @todo reassign original author as a reviewer if present
		$request->AuthorID = $author->ID;
		$request->write();
		
		// assign publishers to this specific request
		foreach($publishers as $publisher) {
			$request->Publishers()->add($publisher);
		}

		// open the request and notify interested parties
		
		$page->flushCache();
		
		return $request;
	}
	
	/**
	 * @param FieldSet $actions
	 * @parma SiteTree $page
	 */
	public static function update_cms_actions(&$actions, $page) {
		$openRequest = $page->OpenWorkflowRequest();

		// if user doesn't have publish rights, exchange the behavior from
		// "publish" to "request publish" etc.
		if(!$page->canPublish() || $openRequest) {
			// "request removal"
			$actions->removeByName('action_deletefromlive');
		}
		
		if(
			!$openRequest
			&& $page->canEdit() 
			//&& $page->stagesDiffer('Stage', 'Live')
			//&& $page->isPublished()
			&& $page->IsDeletedFromStage
		) { 
			$actions->push(
				$requestDeletionAction = new FormAction(
					'cms_requestdeletefromlive', 
					_t('SiteTreeCMSWorkflow.BUTTONREQUESTREMOVAL', 'Request Removal')
				)
			);
			
			// don't allow creation of a second request by another author
			if(!self::can_create(null, $page)) {
				$actions->makeFieldReadonly($requestDeletionAction->Name());
			}
		}
		
		// @todo deny deletion
	}
	
	/**
	 * Approve a deletion request, deleting the page from the live site
	 */
	public function approve($comment, $member = null, $notify = true) {
		if(parent::approve($comment, $member, $notify)) {
			$page = $this->Page();
			$page->deleteFromStage('Live');
			// @todo Coupling to UI :-(
			FormResponse::add(LeftAndMain::deleteTreeNodeJS($page));
			return true;
		}
	}
	
	/**
	 * Return the page for a deletion request.  This is a little tricky because it's not in the stage site
	 */
	public function Page() {
		$page = Versioned::get_latest_version('SiteTree', $this->PageID);
		$page->ID = $page->RecordID;
		return $page;
	}
	
	/**
	 * @param Member $member
	 * @param SiteTree $page
	 * @return boolean
	 */
	public static function can_create($member = NULL, $page) {
		if(!$member && $member !== FALSE) {
			$member = Member::currentUser();
		}

		// if user can't edit page, he shouldn't be able to request publication
		if(!$page->canEdit($member)) return false;

		$request = $page->OpenWorkflowRequest();

		// if a request from a different classname exists, we can't allow creation of a new one
		if($request && $request->ClassName != 'WorkflowDeletionRequest') return false;

		// if no request exists, allow creation of a new one (we can just have one open request at each point in time)
		if(!$request || !$request->ID) return true;

		// members can re-submit their own publication requests
		if($member && $member->ID == $request->AuthorID) return true;

		return false;
	}
	
	function provideI18nEntities() {
		$entities = array();
		$entities["{$this->class}.EMAIL_SUBJECT_AWAITINGAPPROVAL"] = array(
			"Please review and delete the \"%s\" page on your site",
			PR_MEDIUM,
			'Email subject with page title'
		);
		$entities["{$this->class}.EMAIL_SUBJECT_APPROVED"] = array(
			"Your deletion request for the \"%s\" page has been approved",
			PR_MEDIUM,
			'Email subject with page title'
		);
		$entities["{$this->class}.EMAIL_SUBJECT_DENIED"] = array(
			"Your deletion request for the \"%s\" page has been denied",
			PR_MEDIUM,
			'Email subject with page title'
		);
		$entities["{$this->class}.EMAIL_SUBJECT_AWAITINGEDIT"] = array(
			"You are requested to review the \"%s\" page",
			PR_MEDIUM,
			'Email subject with page title'
		);
		
		return $entities;
	}
}
?>