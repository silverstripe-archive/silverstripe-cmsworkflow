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
		return $this->workflowAction('WorkflowPublicationRequest', 'request', $data['ID'], $data['WorkflowComment'],
			_t('SiteTreeCMSWorkflow.REQUEST_PUBLICATION_SUCCESS_MESSAGE','Emailed %s requesting publication')
		);
	}
	
	public function cms_requestdeletefromlive($data, $form) {
		return $this->workflowAction('WorkflowDeletionRequest', 'request', $data['ID'], $data['WorkflowComment'],
			_t('SiteTreeCMSWorkflow.REQUEST_DELETEFROMLIVE_SUCCESS_MESSAGE','Emailed %s requesting deletion')
		);
	}

	// Approve
	public function cms_approve($data, $form) {
		return $this->workflowAction('WorkflowRequest', 'approve', $data['ID'], $data['WorkflowComment'],
			_t('SiteTreeCMSWorkflow.PUBLISHMESSAGE','Approved request and published to the live version. Emailed %s.')
		);
	}
	public function cms_publishwithcomment($data, $form) {
		// TODO: This is all copied and pasted form LeftAndMain::save(), basically so that we don't 
		// need to patch core to get the cmsworkflow module working.  but it should be refactored to
		// a common place.
		
		$className = 'SiteTree';
		$result = '';

		$SQL_id = Convert::raw2sql($data['ID']);
		if(is_numeric($SQL_id)) {
			$bt = defined('Database::USE_ANSI_SQL') ? "\"" : "`";
			$record = DataObject::get_one($className, "{$bt}$className{$bt}.ID = {$SQL_id}");
			if($record && !$record->canEdit()) return Security::permissionFailure($this);
		} else {
			return new HTTPReponse('Bad ID', 400);
		}

		// We don't want to save a new version if there are no changes
		$dataFields_new = $form->Fields()->dataFields();
		$dataFields_old = $record->getAllFields();
		$changed = false;
		$hasNonRecordFields = false;
		foreach($dataFields_new as $datafield) {
			// if the form has fields not belonging to the record
			if(!isset($dataFields_old[$datafield->Name()])) {
				$hasNonRecordFields = true;
			}
			// if field-values have changed
			if(!isset($dataFields_old[$datafield->Name()]) || $dataFields_old[$datafield->Name()] != $datafield->dataValue()) {
				$changed = true;
			}
		}

		if(!$changed && !$hasNonRecordFields) {
			// Tell the user we have saved even though we haven't, as not to confuse them
			if(is_a($record, "Page")) {
				$record->Status = "Saved (update)";
			}
			FormResponse::status_message(_t('LeftAndMain.SAVEDUP',"Saved"), "good");
			FormResponse::update_status($record->Status);
			return FormResponse::respond();
		}

		$form->dataFieldByName('ID')->Value = 0;

		if(isset($urlParams['Sort']) && is_numeric($urlParams['Sort'])) {
			$record->Sort = $urlParams['Sort'];
		}

		// HACK: This should be turned into something more general
		$originalClass = $record->ClassName;
		$originalStatus = $record->Status;
		$originalParentID = $record->ParentID;

		$record->HasBrokenLink = 0;
		$record->HasBrokenFile = 0;

		$record->writeWithoutVersion();

		// HACK: This should be turned into something more general
		$originalURLSegment = $record->URLSegment;

		$form->saveInto($record, true);

		if(is_a($record, "Page")) {
			$record->Status = ($record->Status == "New page" || $record->Status == "Saved (new)") ? "Saved (new)" : "Saved (update)";
		}

		if(Director::is_ajax()) {
			if($SQL_id != $record->ID) {
				FormResponse::add("$('sitetree').setNodeIdx(\"{$SQL_id}\", \"$record->ID\");");
				FormResponse::add("$('Form_EditForm').elements.ID.value = \"$record->ID\";");
			}

			if($added = DataObjectLog::getAdded('SiteTree')) {
				foreach($added as $page) {
					if($page->ID != $record->ID) $result .= $this->addTreeNodeJS($page);
				}
			}
			if($deleted = DataObjectLog::getDeleted('SiteTree')) {
				foreach($deleted as $page) {
					if($page->ID != $record->ID) $result .= $this->deleteTreeNodeJS($page);
				}
			}			
			if($changed = DataObjectLog::getChanged('SiteTree')) {
				foreach($changed as $page) {
					if($page->ID != $record->ID) {
						$title = Convert::raw2js($page->TreeTitle());
						FormResponse::add("$('sitetree').setNodeTitle($page->ID, \"$title\");");
					}
				}
			}

			$message = _t('LeftAndMain.SAVEDUP');

			// Update the class instance if necessary
			if($originalClass != $record->ClassName) {
				$newClassName = $record->ClassName;
				// The records originally saved attribute was overwritten by $form->saveInto($record) before.
				// This is necessary for newClassInstance() to work as expected, and trigger change detection
				// on the ClassName attribute
				$record->setClassName($originalClass);
				// Replace $record with a new instance
				$record = $record->newClassInstance($newClassName);

				// update the tree icon
				FormResponse::add("if(\$('sitetree').setNodeIcon) \$('sitetree').setNodeIcon($record->ID, '$originalClass', '$record->ClassName');");
			}

			FormResponse::add("$('Form_EditForm').getPageFromServer($record->ID);");

			// After reloading action
			if($originalStatus != $record->Status) {
				$message .= sprintf(_t('LeftAndMain.STATUSTO',"  Status changed to '%s'"),$record->Status);
			}

			if($originalParentID != $record->ParentID) {
				FormResponse::add("if(\$('sitetree').setNodeParentID) \$('sitetree').setNodeParentID($record->ID, $record->ParentID);");
			}

			$record->write();

			if( ($record->class != 'VirtualPage') && $originalURLSegment != $record->URLSegment) {
				$message .= sprintf(_t('LeftAndMain.CHANGEDURL',"  Changed URL to '%s'"),$record->URLSegment);
				FormResponse::add("\$('Form_EditForm').elements.URLSegment.value = \"$record->URLSegment\";");
				FormResponse::add("\$('Form_EditForm_StageURLSegment').value = \"{$record->URLSegment}\";");
			}
			
			// *** THIS IS THE ONLY PART THAT'S NOT COPIED FROM LEFTANDMAIN ***
			$this->workflowAction('WorkflowPublicationRequest', 'approve', $data['ID'], $data['WorkflowComment'],
				_t('SiteTreeCMSWorkflow.PUBLISHMESSAGE','Approved request and published changes to live version. Emailed %s.')
			);
			// *** END ***

			// Update classname with original and get new instance (see above for explanation)
			$record->setClassName($originalClass);
			$publishedRecord = $record->newClassInstance($record->ClassName);

			// This will also include the FormResponse stuff that workflowAction() adds.
			return $this->owner->tellBrowserAboutPublicationChange(
				$publishedRecord, 
				null
			);
		}
	}

	// Request edit
	public function cms_requestedit($data, $form) {
		return $this->workflowAction('WorkflowRequest', 'requestedit', $data['ID'], $data['WorkflowComment'],
			_t('SiteTreeCMSWorkflow.DENYPUBLICATION_MESSAGE','Denied workflow request, and reset content. Emailed %s')
		);
	}

	// Deny - ie, cancel the workflow change
	public function cms_deny($data, $form) {
		return $this->workflowAction('WorkflowRequest', 'deny', $data['ID'], $data['WorkflowComment'],
			_t('SiteTreeCMSWorkflow.DENYPUBLICATION_MESSAGE','Denied workflow request, and reset content. Emailed %s')
		);
	}
	
	// Comment (no workflow status change)
	public function cms_comment($data, $form) {
		return $this->workflowAction('WorkflowRequest', 'comment', $data['ID'], $data['WorkflowComment'],
			_t('SiteTreeCMSWorkflow.COMMENT_MESSAGE','Commented on this workflow request. Emailed %s.')
		);
	}

	/**
	 * Process a workflow action.
	 * @param string $workflowClass The sub-class of WorkflowRequest that is expected.
	 * @param string $actionName The action method to call on the given WorkflowRequest objec.t
	 * @param int $id The ID# of the page.
	 * @param string $comment The comment to attach.
	 * @param string $successMessage The message to show on success.
	 */
	function workflowAction($workflowClass,  $actionName, $id, $comment, $successMessage) {
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
		$notify = !($actionName == 'approve' && !$page->openWorkflowRequest($workflowClass));
		
		if($request = $page->openOrNewWorkflowRequest($workflowClass, $notify)) {
			if($request->$actionName($comment, null, $notify)) {
				FormResponse::get_page($id);

				$title = Convert::raw2js($page->TreeTitle());
				FormResponse::add("$('sitetree').setNodeTitle($id, \"$title\");");
		
				// gather members for status output
				if($notify) {
					$emails = implode(", ", $page->PublisherMembers()->column('Email'));
				} else {
					$emails = "no-one";
				}
				FormResponse::status_message(sprintf($successMessage, $emails), 'good');
				return FormResponse::respond();
			}
		}

		// Failure
		FormResponse::status_message(_t('SiteTreeCMSWorkflow.WORKFLOW_ACTION_FAILED', 
			"There was an error when processing your workflow request."), 'bad');
		return FormResponse::respond();
	}
	

}
?>