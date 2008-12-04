<?php
/**
 * A "publication request" is created when an author without
 * publish rights changes a page in draft mode, and explicitly
 * requests it to be reviewed for publication.
 * Each request can have one or more "Publishers" which
 * should have permissions to publish the specific page.
 * 
 * @package cmsworkflow
 */
class WorkflowPublicationRequest extends WorkflowRequest implements i18nEntityProvider {

	/**
	 * @param string $emailtemplate_creation
	 */
	protected static $emailtemplate_awaitingapproval = 'PublicationAwaitingApprovalEmail';
	
	/**
	 * @param string $emailtemplate_approved
	 */
	protected static $emailtemplate_approved = 'WorkflowGenericEmail';
	
	/**
	 * @param string $emailtemplate_declined
	 */
	protected static $emailtemplate_declined = 'WorkflowGenericEmail';
	
	/**
	 * @param string $emailtemplate_awaitingedit
	 */
	protected static $emailtemplate_awaitingedit = 'WorkflowGenericEmail';
	
	function provideI18nEntities() {
		$entities = array();
		$entities["{$this->class}.EMAIL_SUBJECT_AWAITINGAPPROVAL"] = array(
			"Please review and publish the \"%s\" page on your site",
			PR_MEDIUM,
			'Email subject with page title'
		);
		$entities["{$this->class}.EMAIL_SUBJECT_APPROVED"] = array(
			"Your publication request for the \"%s\" page has been denied",
			PR_MEDIUM,
			'Email subject with page title'
		);
		$entities["{$this->class}.EMAIL_SUBJECT_DECLINED"] = array(
			"Your publication request for the \"%s\" page has been denied",
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