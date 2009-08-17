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
	
	public static function create_for_page($page, $author = null, $approvers = null) {
		if(!$author && $author !== FALSE) $author = Member::currentUser();
		
		if(!WorkflowDeletionRequest::can_create($author, $page)) {
			return null;
		}
		
		// take all members from the PublisherGroups relation on this record as a default
		if(!$approvers) $approvers = $page->whoCanApprove();
		
		// if no publishers are set, the request will end up nowhere
		if(!$approvers->Count()) {
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
		foreach($approvers as $approver) {
			$request->Approvers()->add($approver);
		}
		
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
	
	public function publish($comment, $member, $notify) {
		if(!$member) $member = Member::currentUser();
		
		// We have to mark as completed now, or we'll get
		// recursion from SiteTreeCMSWorkflow::onAfterPublish.
		$this->Status = 'Completed';
		$this->PublisherID = $member->ID;
		$this->write();

		$page = $this->Page();
		$page->deleteFromStage('Live');

		// @todo Coupling to UI :-(
		FormResponse::add(LeftAndMain::deleteTreeNodeJS($page));
		
		if ($notify) {
			// notify
		}
		
		return _t('SiteTreeCMSWorkflow.PUBLISHMESSAGE','Published changes to live version. Emailed %s.');
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
			"Page deletion requested: %s",
			PR_MEDIUM,
			'Email subject with page title'
		);
		$entities["{$this->class}.EMAIL_PARA_AWAITINGAPPROVAL"] = array(
			"%s has asked that you delete the \"%s\" page",
			PR_MEDIUM,
			'Intro paragraph for workflow email, with a name and a page title'
		);

		$entities["{$this->class}.EMAIL_SUBJECT_APPROVED"] = array(
			"Page deleted from published site: \"%s\"",
			PR_MEDIUM,
			'Email subject with page title'
		);
		$entities["{$this->class}.EMAIL_PARA_APPROVED"] = array(
			"%s has approved your request to delete the \"%s\" page and deleted it from the published site.",
			PR_MEDIUM,
			'Intro paragraph for workflow email, with a name and a page title'
		);

		$entities["{$this->class}.EMAIL_SUBJECT_DENIED"] = array(
			"Deletion rejected: \"%s\"",
			PR_MEDIUM,
			'Email subject with page title'
		);
		$entities["{$this->class}.EMAIL_PARA_DENIED"] = array(
			"%s has rejected your request to dele the \"%s\" page.",
			PR_MEDIUM,
			'Intro paragraph for workflow email, with a name and a page title'
		);

		$entities["{$this->class}.EMAIL_SUBJECT_AWAITINGEDIT"] = array(
			"Revision requested: \"%s\"",
			PR_MEDIUM,
			'Email subject with page title'
		);
		$entities["{$this->class}.EMAIL_PARA_AWAITINGEDIT"] = array(
			"%s asked you to revise your request to delete the \"%s\" page.",
			PR_MEDIUM,
			'Intro paragraph for workflow email, with a name and a page title'
		);

		$entities["{$this->class}.EMAIL_SUBJECT_COMMENT"] = array(
			"Comment on deletion request: \"%s\"",
			PR_MEDIUM,
			'Email subject with page title'
		);
		$entities["{$this->class}.EMAIL_PARA_COMMENT"] = array(
			"%s commented on the request to delete the \"%s\" page.",
			PR_MEDIUM,
			'Intro paragraph for workflow email, with a name and a page title'
		);

		return $entities;
	}
}
?>