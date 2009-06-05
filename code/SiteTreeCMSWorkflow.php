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
class SiteTreeCMSWorkflow extends DataObjectDecorator implements PermissionProvider {
	
	/**
	 * A registry of all allowed request classes.
	 * 
	 * @var Array $allowed_request_classes 
	 */
	protected static $allowed_request_classes = array();
	
	/**
	 * @param string $requestClass
	 * @return bool
	 */
	public static function register_request($requestClass) {
		if(class_exists($requestClass) == false)
			return false;

		if(is_subclass_of($requestClass, 'WorkflowRequest') == false)
			return false;

		if(in_array($requestClass, self::$allowed_request_classes) == false) {
			array_push(self::$allowed_request_classes, $requestClass);
		}

		return true;
	}
	
	/**
	 * @param string $requestClass
	 * @return bool Returns TRUE on success, FALSE otherwise.
	 */
	public static function unregister_request($requestClass) {
		if(in_array($requestClass, self::$authenticators)) {
			unset(self::$allowed_request_classes[array_search($requestClass, self::$allowed_request_classes)]);
		}
	}
	
	function extraStatics() {
		return array(
			'db' => array(
				"CanPublishType" =>"Enum('LoggedInUsers, OnlyTheseUsers', 'OnlyTheseUsers')", 
				"ReviewPeriodDays" => "Int",
				"NextReviewDate" => "Date",
			),
			'has_one' => array(
				'Owner' => 'Member',
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

		if($wf = $this->openWorkflowRequest()) {
			$verb = ($wf->class == "WorkflowDeletionRequest") ? "Removal " : "Change ";
			$fields->fieldByName('Root')->insertBefore(new Tab($verb . $wf->StatusDescription,
				new LiteralField("WorkflowInfo", $this->owner->renderWith("SiteTreeCMSWorkflow_workflowtab"))
			), "Content");
		}
		
		// Review fields
		$cmsUsers = Permission::get_members_by_permission(array("CMS_ACCESS_CMSMain", "ADMIN"));
		
		if(Permission::check("EDIT_CONTENT_REVIEW_FIELDS")) {
			$fields->addFieldsToTab("Root.Access", array(
				new HeaderField(_t('SiteTreeCMSWorkflow.REVIEWHEADER', "Content review"), 2),
				new DropdownField("OwnerID", _t("SiteTreeCMSWorkflow.PAGEOWNER", 
					"Page owner (will be responsible for reviews)"), $cmsUsers->map('ID', 'Title', '(no owner)')),
				new CalendarDateField("NextReviewDate", _t("SiteTreeCMSWorkflow.NEXTREVIEWDATE",
					"Next review date (leave blank for no review)")),
				new DropdownField("ReviewPeriodDays", _t("SiteTreeCMSWorkflow.REVIEWFREQUENCY", 
					"Review frequency (the review date will be set to this far in the future whenever the page is published.)"), array(
					0 => "No automatic review date",
					1 => "1 day",
					7 => "1 week",
					30 => "1 month",
					60 => "2 months",
					91 => "3 months",
					121 => "4 months",
					152 => "5 months",
					183 => "6 months",
					365 => "12 months",
				)),
			));
		}
		
		$fields->addFieldsToTab('Root.Workflow', $this->getWorkflowCMSFields());
	}
	
	/**
	 * @return FieldSet
	 */
	public function getWorkflowCMSFields() {
		$fields = new FieldSet();
		
		$diffLinkTitle = _t('SiteTreeCMSWorkflow.DIFFERENCESLINK', 'Show differences to live');

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
		if(self::$allowed_request_classes) foreach(self::$allowed_request_classes as $class) {
			// @todo Workaround: calling static method as instance method to avoid eval()
			singleton($class)->update_cms_actions($actions, $this->owner);
		}
	}
	
	/**
	 * Returns actions for the worfklow tab.
	 * @todo This is a pretty clear example of user-interface logic baked into the model.  We 
	 * should solve this at a Sapphire-framework level, somehow.
	 */
	public function WorkflowActions() {
		$actions = $this->openWorkflowRequest()->WorkflowActions();
		$output = new DataObjectSet();
		foreach($actions as $code => $title) {
			$output->push(new ArrayData(array(
				'Action' => "action_$code",
				'Title' => Convert::raw2xml($title),
			)));			
		}
		return $output;
	}
	
	/**
	 * Returns a DataObjectSet of all the members that can publish this page
	 */
	public function PublisherMembers() {
		if($this->owner->CanPublishType == 'OnlyTheseUsers'){
			$groups = $this->owner->PublisherGroups();
			$members = new DataObjectSet();
			if($groups) foreach($groups as $group) {
				$members->merge($group->Members());
			}
			
			// Default to ADMINs, if something goes wrong
			if(!$members->Count()) {
				$group = Permission::get_groups_by_permission('ADMIN')->first();
				$members = $group->Members();
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
	/*
	public function OpenWorkflowRequest($filter = "", $sort = "", $join = "", $limit = "") {
		$this->componentCache = array();
		
		if($filter) $filter .= ' AND ';
		$filter .= "Status NOT IN ('Approved','Denied')";
		return $this->owner->getComponents(
			'WorkflowRequests',
			$filter,
			$sort,
			$join,
			$limit
		)->First();
	}
	*.

	/**
	 * Return a workflow request which has not already been
	 * approved or declined.
	 * 
	 * @return DataObjectSet Set of WorkflowRequest objects
	 */
	public function ClosedWorkflowRequests($filter = "", $sort = "", $join = "", $limit = "") {
		$this->componentCache = array();
		
		if($filter) $filter .= ' AND ';
		$filter .= "Status IN ('Approved','Denied')";
		return $this->owner->getComponents(
			'WorkflowRequests',
			$filter,
			$sort,
			$join,
			$limit
		);
	}

	public function openWorkflowRequest($workflowClass = 'WorkflowRequest') {
		if(is_subclass_of($workflowClass, 'WorkflowRequest') || $workflowClass == 'WorkflowRequest') {
			if((int)$this->owner->ID) {
				$wf = DataObject::get_one($workflowClass, "PageID = " . (int)$this->owner->ID . " AND Status NOT IN ('Approved','Denied')");
				if($wf) return $wf;
				else return null;
			}
		} else {
			user_error("SiteTreeCMSWorkflow::openWorkflowRequest(): Bad workflow class '$workflowClass'", E_USER_WARNING);
		}
	}
	
	/**
	 * @param $workflowClass The class of the workflow object to create
	 * @param $notify Should I sent out emails notifying people of workflow activities?
	 */
	public function openOrNewWorkflowRequest($workflowClass, $notify = true) {
		if($wf = $this->openWorkflowRequest($workflowClass)) {
			return $wf;
		} else if(is_subclass_of($workflowClass, 'WorkflowRequest')) {
			// TODO: How to avoid eval() here?
			return eval("return $workflowClass::create_for_page(\$this->owner, null, null, \$notify);");
		} else {
			user_error("SiteTreeCMSWorkflow::openOrNewWorkflowRequest(): Bad workflow class '$workflowClass'", E_USER_WARNING);
		}
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
	
	// Event handlers for the built-in actions will tidy up any open workflow that's leftover.
	// If a workflow action is calling publish/delete/etc, the workflow object should be cleaned up
	// *before* the action is called.
	
	/**
	 * After publishing remove from the report of items needing publication 
	 */
	function onAfterPublish() {
		if($wf = $this->openWorkflowRequest()) {
			if(get_class($wf) == 'WorkflowPublicationRequest') $wf->approve("(automatically approved)");
			else $wf->deny("(automatically denied when the page was published)");
		}
	}
	
	/**
	 * 
	 */
	function onAfterDelete() {
		if($wf = $this->openWorkflowRequest()) {
			if(get_class($wf) == 'WorkflowDeletionRequest') $wf->approve("(automatically approved)");
			else $wf->deny("(automatically denied when the page was deleted)");
		}
	}
	
	function onAfterRevertToLive() {
		if($wf = $this->openWorkflowRequest()) {
			$wf->deny("(automatically denied)");
		}
	}
	
	function providePermissions() {
		return array(
			"EDIT_CONTENT_REVIEW_FIELDS" => "Can edit the 'Content review' fields on each page",
		);
	}

}
?>