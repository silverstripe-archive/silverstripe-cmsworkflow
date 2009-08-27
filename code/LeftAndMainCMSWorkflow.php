<?php
class LeftAndMainCMSWorkflow extends LeftAndMainDecorator {
	
	public static $allowed_actions = array(
		'cms_requestpublication',
		'cms_requestdeletefromlive',
		'cms_denypublication',
		'cms_denydeletion'
	);
	
	function init() {
		// We need to make sure these CMSMain scripts are included first
		Requirements::javascript('cms/javascript/CMSMain.js');
		Requirements::javascript('cms/javascript/CMSMain_left.js');
		Requirements::javascript('cms/javascript/CMSMain_right.js');

		Requirements::javascript('cmsworkflow/javascript/LeftAndMainCMSWorkflow.js');
		RSSFeed::linkToFeed(Director::absoluteURL('admin/cms/changes.rss'), 'All content changes');
	}
	
	// Request
	
	/**
	 * Handler for the CMS button
	 */
	public function cms_requestpublication($data, $form) {
		return $this->workflowAction('WorkflowPublicationRequest', 'request', $data['ID'], $data['WorkflowComment']);
	}
	
	public function cms_requestdeletefromlive($data, $form) {
		return $this->workflowAction('WorkflowDeletionRequest', 'request', $data['ID'], $data['WorkflowComment']);
	}

	// Approve
	public function cms_approve($data, $form) {
		return $this->workflowAction('WorkflowRequest', 'approve', $data['ID'], $data['WorkflowComment']);
	}
	
	/**
	 * When a page is saved, we need to check if there is an in-progress
	 * workflow request, and if applicable, set it back to AwaitingApproval
	 */
	public function onAfterSave($record) {
		if($record->hasMethod('openWorkflowRequest') && $wf = $record->openWorkflowRequest()) {
			if ($wf->Status != 'AwaitingApproval') {
				$wf->request("Page was resaved, automatically set workflow request back to awaiting approval", null, false);
				FormResponse::add("$('Form_EditForm').getPageFromServer($record->ID);");
			}
		}
	}
	
	// Request edit
	public function cms_requestedit($data, $form) {
		return $this->workflowAction('WorkflowRequest', 'requestedit', $data['ID'], $data['WorkflowComment']);
	}

	// Deny - ie, cancel the workflow change
	public function cms_deny($data, $form) {
		return $this->workflowAction('WorkflowRequest', 'deny', $data['ID'], $data['WorkflowComment']);
	}
	
	// Comment (no workflow status change)
	public function cms_comment($data, $form) {
		return $this->workflowAction('WorkflowRequest', 'comment', $data['ID'], $data['WorkflowComment']);
	}

	/**
	 * Process a workflow action.
	 * @param string $workflowClass The sub-class of WorkflowRequest that is expected.
	 * @param string $actionName The action method to call on the given WorkflowRequest objec.t
	 * @param int $id The ID# of the page.
	 * @param string $comment The comment to attach.
	 * @param string $successMessage The message to show on success.
	 */
	function workflowAction($workflowClass,  $actionName, $id, $comment) {
		if(is_numeric($id)) {
			// For 2.3 and 2.4 compatibility
			$bt = defined('Database::USE_ANSI_SQL') ? "\"" : "`";

			$page = DataObject::get_by_id("SiteTree", $id);
			if(!$page) $page = Versioned::get_one_by_stage("SiteTree", "Live", "{$bt}SiteTree{$bt}.ID = $id");
			if(!$page) return new HTTPResponse("Can't find Page #$id", 400);
		} else {
			return new HTTPResponse("Bad ID", 400);
		}
		
		// If we are creating and approving a workflow in one step, then don't bother emailing
		$notify = !($actionName == 'action' && !$page->openWorkflowRequest($workflowClass));
		
		if($request = $page->openOrNewWorkflowRequest($workflowClass, $notify)) {
			$request->clearMembersEmailed();

			if($successMessage = $request->$actionName($comment, null, $notify)) {
				FormResponse::get_page($id);

				$title = Convert::raw2js($page->TreeTitle());
				FormResponse::add("$('sitetree').setNodeTitle($id, \"$title\");");
		
				// gather members for status output
				if($notify) {
					$peeps = $request->getMembersEmailed();
					if ($peeps && $peeps->Count()) {
						$emails = '';
						foreach($peeps as $peep) {
							if ($peep->Email) $emails .= $peep->Email.', ';
						}
						$emails = trim($emails, ', ');
					} else { $emails = 'no-one'; }
				} else {
					$emails = "no-one";
				}
				
				if ($successMessage) {
					FormResponse::status_message(sprintf($successMessage, $emails), 'good');
					return FormResponse::respond();
				} else {
					return;
				}
			}
		}

		// Failure
		FormResponse::status_message(_t('SiteTreeCMSWorkflow.WORKFLOW_ACTION_FAILED', 
			"There was an error when processing your workflow request."), 'bad');
		return FormResponse::respond();
	}
	

}
?>
