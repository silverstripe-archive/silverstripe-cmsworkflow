<?php
/**
 * Extension to SiteTree for CMS Workflow support.
 * 
 * @todo Currently a publication/deletion approval is implicit by the "save and publish" and "delete from live" actions.
 * This also means that CMS editors not assigned to this workflow, but with publish rights on the page can (unknowingly)
 * end a workflow request. These assumptions are codified in {@link onAfterPublish()} and {@link onAfterDelete()}
 *
 * @package cmsworkflow
 */
class SiteTreeCMSWorkflow extends DataObjectDecorator {
	function extraDBFields() {
		return array(
			'db' => array(
				"CanPublishType" =>"Enum('LoggedInUsers, OnlyTheseUsers', 'OnlyTheseUsers')", 
			),
			'has_many' => array(
				// has_one OpenWorkflowRequest is implemented as custom getter
				'WorkflowRequests' => 'WorkflowRequest', 
			),
			'many_many' => array(
				"PublisherGroups" => "Group",
			),
			'defaults' => array(
				"CanPublishType" => "OnlyTheseUsers",
			),
		);
	}
	
	public function updateCMSFields(&$fields) {
		$fields->addFieldsToTab("Root.Access", array(
			new HeaderField(_t('SiteTreeCMSWorkflow.PUBLISHHEADER', "Who can publish this inside the CMS?"), 2),
			$publishTypeField = new OptionsetField(
				"CanPublishType", 
				"",
				array(
					"LoggedInUsers" => _t('SiteTree.EDITANYONE', "Anyone who can log-in to the CMS"),
					"OnlyTheseUsers" => _t('SiteTree.EDITONLYTHESE', "Only these people (choose from list)")
				),
				"OnlyTheseUsers"
			),
			$publisherGroupsField = new TreeMultiselectField("PublisherGroups", $this->owner->fieldLabel('PublisherGroups'))
		));
		if(!$this->owner->canPublish() || !Permission::check('SITETREE_GRANT_ACCESS')) {
			$fields->replaceField('CanPublishType', $publishTypeField->performReadonlyTransformation());
			$fields->replaceField('PublisherGroups', $publisherGroupsField->performReadonlyTransformation());
		}
		
		$fields->findOrMakeTab('Root.Workflow', _t('SiteTreeCMSWorkflow.WORKFLOWTABTITLE', 'Workflow'));
		$fields->addFieldsToTab('Root.Workflow', $this->getWorkflowCMSFields());
	}
	
	/**
	 * @return FieldSet
	 */
	public function getWorkflowCMSFields() {
		$fields = new FieldSet();
		
		$diffLinkTitle = _t('SiteTreeCMSWorkflow.DIFFERENCESLINK', 'Show differences to live');
		
		// list all open requests
		$fields->push(new HeaderField(
			'WorkflowOpenRequestHeader', 
			_t('SiteTreeCMSWorkflow.OPENREQUESTHEADER', 'Open Requests')
		));
		// @todo more inline view
		$openRequest = $this->OpenWorkflowRequest();
		if($openRequest) {
			$detailFields = $openRequest->getCMSDetailFields();
			// poor man's Form->loadDataFrom()
			$dataFields = $detailFields->dataFields();
			if($dataFields) foreach($dataFields as $field) {
				$name = $field->Name();
				$field->setValue($openRequest->$name);
			}
			$detailFields->removeByName('Page');
			$fields->merge($detailFields->makeReadonly());
		} else {
			$fields->push(new LiteralField(
				'NoOpenRequestsNote',
				sprintf(
					'<p>%s</p>',
					_t(
						'SiteTreeCMSWorkflow.OPENREQUESTSNOFOUND', 
						'No open request found'
					)
				)
			));
		}
		
		
		// list all closed requests
		$fields->push(new HeaderField(
			'WorkflowClosedRequestsHeader', 
			_t('SiteTreeCMSWorkflow.CLOSEDREQUESTSHEADER', 'Closed Requests')
		));
		$closedRequests = $this->ClosedWorkflowRequests();
		$closedRequestsTF = new ComplexTableField(
			$this,
			'ClosedWorkflowRequests',
			'WorkflowRequest',
			array(
				'Created' => singleton('WorkflowRequest')->fieldLabel('Created'), 
				'StatusDescription' => singleton('WorkflowRequest')->fieldLabel('Status'),
				'Author.Title' => singleton('WorkflowRequest')->fieldLabel('Author'),
				'DiffLinkToLastPublished' => _t('SiteTreeCMSWorkflow.DIFFERENCESCOLUMN', 'Differences'),
			)
		);
		$closedRequestsTF->setPermissions(array('show'));
		$closedRequestsTF->setFieldCasting(array(
			'Created' => 'Date->Nice'
		));
		$closedRequestsTF->setFieldFormatting(array(
			"DiffLinkToLastPublished" => '<a href=\"$value\" target=\"_blank\" class=\"externallink\">' . $diffLinkTitle . '</a>'
		));
		$closedRequestsTF->setCustomSourceItems($closedRequests);
		$fields->push($closedRequestsTF);
		
		return $fields;
	}
	
	/**
	 * Normal authors (without publication permission) can perform certain actions on a page,
	 * e.g. "save" and "delete from draft". Other permissions like "publish" or "delete from live"
	 * are hidden based on the {@link SiteTree->canPublish()} permission, and replaced
	 * with triggers for requesting these actions ("request publication" and "request deletion").
	 *
	 * @param FieldSet $actions
	 */
	public function updateCMSActions(&$actions) {
		// if user doesn't have publish rights, exchange the behavior from
		// "publish" to "request publish" etc.
		if(!$this->owner->canPublish()) {

			// authors shouldn't be able to revert, as this republishes the page.
			// they should rather change the page and re-request publication
			$actions->removeByName('action_revert');

			// "request publication"
			$actions->removeByName('action_publish');
			if(
				$this->owner->canEdit() 
				&& $this->owner->stagesDiffer('Stage', 'Live')
				&& $this->owner->Version > 1 // page has been saved at least once
			) { 
				$actions->push(
					$requestPublicationAction = new FormAction(
						'cms_requestpublication', 
						_t('SiteTreeCMSWorkflow.BUTTONREQUESTPUBLICATION', 'Request Publication')
					)
				);
				// don't allow creation of a second request by another author
				if(!WorkflowPublicationRequest::can_create(null, $this->owner)) {
					$actions->makeFieldReadonly($requestPublicationAction->Name());
				}
			}
			
			// "request removal"
			$actions->removeByName('action_deletefromlive');
			if(
				$this->owner->canEdit() 
				&& ($this->owner->stagesDiffer('Stage', 'Live') || $this->owner->DeletedFromStage)
				&& $this->owner->isPublished()
			) { 
				$actions->push(
					$requestDeletionAction = new FormAction(
						'cms_requestdeletefromlive', 
						_t('SiteTreeCMSWorkflow.BUTTONREQUESTREMOVAL', 'Request Removal')
					)
				);
				
				// don't allow creation of a second request by another author
				if(!WorkflowDeletionRequest::can_create(null, $this->owner)) {
					$actions->makeFieldReadonly($requestDeletionAction->Name());
				}
			}
		}
		
		// "deny publication"
		$openRequest = $this->owner->OpenWorkflowRequest();
		if(
			$this->owner->canPublish()
			&& $openRequest
			&& $openRequest instanceof WorkflowPublicationRequest
		) {
			$actions->push(
				$requestDeletionAction = new FormAction(
					'cms_denypublication',
					_t('SiteTreeCMSWorkflow.BUTTONDENYPUBLICATION', 'Deny Publication')
				)
			);
		}
	}
	
	/**
	 * Returns a DataObjectSet of all the members that can publish this page
	 */
	public function PublisherMembers() {
		if($this->owner->CanPublishType == 'OnlyTheseUsers'){
			$groups = $this->owner->PublisherGroups();
			$members = new DataObjectSet();
			foreach($groups as $group) {
				$members->merge($group->Members());
			}
			return $members;
		} else {
			$group = Permission::get_groups_by_permission('ADMIN')->first();
			return $group->Members();
		}
	}
	
	/**
	 * Return a workflow request which has not already been
	 * approved or declined.
	 * 
	 * @return WorkflowRequest
	 */
	public function OpenWorkflowRequest($filter = "", $sort = "", $join = "", $limit = "") {
		$this->componentCache = array();
		
		if($filter) $filter .= ' AND ';
		$filter .= "Status NOT IN ('Approved','Declined')";
		return $this->owner->getComponents(
			'WorkflowRequests',
			$filter,
			$sort,
			$join,
			$limit
		)->First();
	}

	/**
	 * Return a workflow request which has not already been
	 * approved or declined.
	 * 
	 * @return DataObjectSet Set of WorkflowRequest objects
	 */
	public function ClosedWorkflowRequests($filter = "", $sort = "", $join = "", $limit = "") {
		$this->componentCache = array();
		
		if($filter) $filter .= ' AND ';
		$filter .= "Status IN ('Approved','Declined')";
		return $this->owner->getComponents(
			'WorkflowRequests',
			$filter,
			$sort,
			$join,
			$limit
		);
	}

	/**
	 * This function should return true if the current user can view this
	 * page.
	 *
	 * It can be overloaded to customise the security model for an
	 * application.
	 *
	 * @return boolean True if the current user can view this page.
	 */
	public function canPublish($member = null) {
		if(!$member && $member !== FALSE) $member = Member::currentUser();

		// check for admin permission
		if(Permission::checkMember($member, 'ADMIN')) return true;
		
		// check for missing cmsmain permission
		if(!Permission::checkMember($member, 'CMS_ACCESS_CMSMain')) return false;

		// check for empty spec
		if(!$this->owner->CanPublishType || $this->owner->CanPublishType == 'Anyone') return true;

		// check for any logged-in users
		if($this->owner->CanPublishType == 'LoggedInUsers' && !Permission::checkMember($member, 'CMS_ACCESS_CMSMain')) return false;

		// check for specific groups
		if(
			$this->owner->CanPublishType == 'OnlyTheseUsers' 
			&& (
				!$member
				|| !$member->inGroups($this->owner->PublisherGroups())
			)
		) {
			return false;
		}

		return true;
	}
	
	/**
	 * Adds mappings of the default groups created.
	 * @todo Also re-adds default groups if all existing custom groups
	 * are deselected from a record - is this desired behaviour?
	 */
	function onAfterWrite() {
		if(!$this->owner->EditorGroups()->Count()) {
			$SQL_group = Convert::raw2sql('site-content-authors');
			$groupCheckObj = DataObject::get_one('Group', "Code = '{$SQL_group}'");
			if($groupCheckObj) $this->owner->EditorGroups()->add($groupCheckObj);
			
			$SQL_group = Convert::raw2sql('site-content-publishers');
			$groupCheckObj = DataObject::get_one('Group', "Code = '{$SQL_group}'");
			if($groupCheckObj) $this->owner->EditorGroups()->add($groupCheckObj);
		}
		
		if(!$this->owner->PublisherGroups()->Count()) {
			$SQL_group = Convert::raw2sql('site-content-publishers');
			$groupCheckObj = DataObject::get_one('Group', "Code = '{$SQL_group}'");
			if($groupCheckObj) $this->owner->PublisherGroups()->add($groupCheckObj);
		}

	}

	function augmentDefaultRecords() {
		if(!DB::query("SELECT * FROM `Group` WHERE `Group`.`Code` = 'site-content-authors'")->value()){
			$authorGroup = Object::create('Group');
			$authorGroup->Title = 'Site Content Authors';
			$authorGroup->Code = "site-content-authors";
			$authorGroup->write();
			Permission::grant($authorGroup->ID, "CMS_ACCESS_CMSMain");
			Permission::grant($authorGroup->ID, "CMS_ACCESS_AssetAdmin");
			Database::alteration_message("Added site content author group","created");
		}

		if(!DB::query("SELECT * FROM `Group` WHERE `Group`.`Code` = 'site-content-publishers'")->value()){
			$publishersGroup = Object::create('Group');
			$publishersGroup->Title = 'Site Content Publishers';
			$publishersGroup->Code = "site-content-publishers";
			$publishersGroup->write();
			Permission::grant($publishersGroup->ID, "CMS_ACCESS_CMSMain");
			Permission::grant($publishersGroup->ID, "CMS_ACCESS_AssetAdmin");
			Database::alteration_message("Added site content publisher group","created");
		}
	}
	
	/**
	 * After publishing remove from the report of items needing publication 
	 */
	function onAfterPublish() {
		$currentPublisher = Member::currentUser();
		$request = $this->owner->OpenWorkflowRequest();
		// this assumes that a publisher knows about the ongoing approval discussion
		// which might not always be the case
		if($request && $request->ID) {
			$request->PublisherID = $currentPublisher->ID;
			$request->write();
			// open the request and notify interested parties
			$request->Status = 'Approved';
			$request->write();
			$request->notifyApproved();
		}
	}
	
	/**
	 * 
	 */
	function onAfterDelete() {
		$currentPublisher = Member::currentUser();
		$request = $this->owner->OpenWorkflowRequest();
		// this assumes that a publisher knows about the ongoing approval discussion
		// which might not always be the case
		if($request && $request->ID) {
			$request->PublisherID = $currentPublisher->ID;
			$request->write();
			// open the request and notify interested parties
			$request->Status = 'Approved';
			$request->write();
			$request->notifyApproved();
		}
	}
	
	/**
	 * @param Member $member The user requesting publication
	 * @param DataObjectSet $publishers Publishers assigned to this request.
	 * @return boolean|WorkflowPublicationRequest
	 */
	public function requestPublication($author = null, $publishers = null){
		if(!$author && $author !== FALSE) $author = Member::currentUser();
		
		// take all members from the PublisherGroups relation on this record as a default
		if(!$publishers) $publishers = $this->PublisherMembers();

		// if no publishers are set, the request will end up nowhere
		if(!$publishers->Count()) {
			return false;
		}

		if(!WorkflowPublicationRequest::can_create($author, $this->owner)) {
			return false;
		}
		
		// get or create a publication request
		$request = $this->owner->OpenWorkflowRequest();
		if(!$request || !$request->ID) {
			$request = new WorkflowPublicationRequest();
			$request->PageID = $this->owner->ID;
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
		$request->Status = 'AwaitingApproval';
		$request->write();
		$request->notifiyAwaitingApproval();
		
		//$this->owner->flushCache();
		
		return $request;
	}
	
	/**
	 * @param Member $member The user requesting deletion
	 * @param DataObjectSet $publishers Publishers assigned to this request.
	 * @return boolean|WorkflowDeletionRequest
	 */
	public function requestDeletion($author = null, $publishers = null){
		if(!$author && $author !== FALSE) $author = Member::currentUser();
		
		if(!WorkflowDeletionRequest::can_create($author, $this->owner)) {
			return false;
		}
		
		// take all members from the PublisherGroups relation on this record as a default
		if(!$publishers) $publishers = $this->PublisherMembers();
		
		// if no publishers are set, the request will end up nowhere
		if(!$publishers->Count()) {
			return false;
		}
		
		// get or create a publication request
		$request = $this->owner->OpenWorkflowRequest();
		if(!$request || !$request->ID) {
			$request = new WorkflowDeletionRequest();
			$request->PageID = $this->owner->ID;
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
		$request->Status = 'AwaitingApproval';
		$request->write();
		$request->notifiyAwaitingApproval();
		
		$this->owner->flushCache();
		
		return $request;
	}
	
	/**
	 * Denies the request, and restores the page from live.
	 * This might cause draft modifications independent of this publication request
	 * to be reverted as well, but thats a necessary evil.
	 * 
	 * @uses SiteTree->doRevertToLive()
	 * 
	 * @param Member $member The user denying the publication
	 * @return boolean|WorkflowDeletionRequest
	 */
	public function denyPublication($author = NULL){
		if(!$author && $author !== FALSE) $author = Member::currentUser();
		
		// if the author can't publish, he shouldn't be allowed to deny this action either
		if(!$this->owner->canPublish($author)) {
			return false;
		}
		
		// get or create a publication request
		$request = $this->owner->OpenWorkflowRequest();
		if(!$request) return false;
		
		// revert page to live (which might undo independent changes by other authors)
		$this->owner->doRevertToLive();
		
		// open the request and notify interested parties
		$request->Status = 'Denied';
		$request->write();
		$request->notifyDenied();
		
		$this->owner->flushCache();
		
		return $request;
	}
}
?>