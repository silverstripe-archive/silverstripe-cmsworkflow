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
	protected static $emailtemplate_denied = 'WorkflowGenericEmail';
	
	/**
	 * @param string $emailtemplate_awaitingedit
	 */
	protected static $emailtemplate_awaitingedit = 'WorkflowGenericEmail';
	
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