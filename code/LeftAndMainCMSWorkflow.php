<?php
class LeftAndMainCMSWorkflow extends LeftAndMainDecorator {
	
	public static $allowed_actions = array(
		'cms_requestpublication',
		'cms_requestdeletefromlive',
		'cms_denypublication',
		'cms_denydeletion'
	);
	
	function init() {
		Requirements::javascript('cmsworkflow/javascript/LeftAndMainCMSWorkflow.js');
	}
	
	/**
	 * Handler for the CMS button
	 */
	public function cms_requestpublication($urlParams, $form) {
		$id = $urlParams['ID'];
		$page = DataObject::get_by_id("SiteTree", $id);
		
		// request publication
		$request = WorkflowPublicationRequest::create_for_page($page);
		if(!$request) return false;
		
		// gather members for status output
		$members = $page->PublisherMembers();
		foreach($members as $member) {
			$emails[] = $member->Email;
		}
		$strEmails = implode(", ", $emails);
		
		FormResponse::status_message(
			sprintf(
				_t('SiteTreeCMSWorkflow.REQUEST_PUBLICATION_SUCCESS_MESSAGE','Emailed %s requesting publication'), 
				$strEmails
			), 
			'good'
		);
		return FormResponse::respond();	
	}
	
	public function cms_requestdeletefromlive($urlParams, $form) {
		$id = $urlParams['ID'];
		$page = DataObject::get_by_id("SiteTree", $id);
		
		if(!WorkflowDeletionRequest::can_create(null, $page)) {
			return false;
		}
		
		// request publication
		$request = WorkflowDeletionRequest::create_for_page($page);
		if(!$request) return false;
		
		// gather members for status output
		$members = $page->PublisherMembers();
		foreach($members as $member) {
			$emails[] = $member->Email;
		}
		$strEmails = implode(", ", $emails);
		
		FormResponse::status_message(
			sprintf(
				_t('SiteTreeCMSWorkflow.REQUEST_DELETEFROMLIVE_SUCCESS_MESSAGE','Emailed %s requesting deletion'), 
				$strEmails
			), 
			'good'
		);
		return FormResponse::respond();	
	}
	
	public function cms_denypublication($urlParams, $form) {
		$id = $urlParams['ID'];
		$page = DataObject::get_by_id("SiteTree", $id);
		
		// request publication
		$request = $page->OpenWorkflowRequest();
		if(!$request) return false;
		
		if(!($request instanceof WorkflowPublicationRequest)) return false;
		
		$success = $request->deny(Member::currentUser());
		
		// gather members for status output
		$members = $page->PublisherMembers();
		foreach($members as $member) {
			$emails[] = $member->Email;
		}
		$strEmails = implode(", ", $emails);
		
		FormResponse::status_message(
			sprintf(
				_t('SiteTreeCMSWorkflow.DENYPUBLICATION_MESSAGE','Denied request and reset page to live version. Emailed %s d'), 
				$strEmails
			), 
			'good'
		);
		return FormResponse::respond();
	}
	
	public function cms_denydeletion($urlParams, $form) {
		$id = $urlParams['ID'];
		$page = DataObject::get_by_id("SiteTree", $id);
		
		// request publication
		$request = $page->OpenWorkflowRequest();
		if(!$request) return false;
		
		if(!($request instanceof WorkflowDeletionRequest)) return false;
		
		$success = $request->deny(Member::currentUser());
		
		// gather members for status output
		$members = $page->PublisherMembers();
		foreach($members as $member) {
			$emails[] = $member->Email;
		}
		$strEmails = implode(", ", $emails);
		
		FormResponse::status_message(
			sprintf(
				_t('SiteTreeCMSWorkflow.DENYDELECTIONMESSAGE','Denied request and reset page to live version. Emailed %s d'), 
				$strEmails
			), 
			'good'
		);
		return FormResponse::respond();
	}
	
	// further actions like "save and publish" as well as "delete" are implicitly
	// connected with the workflow through onBefore*() callbacks

}
?>