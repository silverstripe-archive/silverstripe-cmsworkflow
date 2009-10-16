<?php

/**
 * Augment SiteTree with a new permissions, 'canApprove',
 * and 'canAction'.
 *
 * @package cmsworkflow
 * @subpackage threestep
 * @author Tom Rix
 */
class SiteTreeCMSThreeStepWorkflow extends SiteTreeCMSWFDecorator implements PermissionProvider {
	public function extraStatics() {
		return array(
			'db' => array(
				"CanApproveType" =>"Enum('LoggedInUsers, OnlyTheseUsers, Inherit', 'Inherit')",
				"CanPublishType" =>"Enum('LoggedInUsers, OnlyTheseUsers, Inherit', 'Inherit')"
			),
			'many_many' => array(
				"ApproverGroups" => "Group",
				"PublisherGroups" => "Group",
			),
			'defaults' => array(
				"CanApproveType" => "OnlyTheseUsers",
				"CanPublishType" => "OnlyTheseUsers",
			)
		);
	}
	
	function getOpenRequest($workflowClass) {
		$wf = DataObject::get_one($workflowClass, "PageID = " . (int)$this->owner->ID . " AND Status NOT IN ('Completed', 'Denied')");
		if($wf) return $wf;
		
		return null;
	}
	
	function batchPublish() {
		$page = $this->owner;
		if($request = $page->openWorkflowRequest('WorkflowRequest')) {
			if ($request->Status == 'AwaitingApproval') {
				$request->approve('Batch approval', null, false);
				$request->publish('Batch publish', null, false);
			}
			if ($request->Status == 'Approved') {
				$request->publish('Batch publish', null, false);
			}
		}
	}
	function batchApprove() {
		$page = $this->owner;
		if($request = $page->openWorkflowRequest('WorkflowRequest')) {
			if ($request->Status == 'AwaitingApproval') {
				$request->approve('Batch approval', null, false);
			}
		}
	}
	
	function canDenyRequests() {
		return true;
	}
	
	function canRequestEdit() {
		return true;
	}
	
	function whoCanApprove() {
		return $this->ApproverMembers();
	}
	
	/**
	 * Implement permissions for ThreeStep
	 *
	 * @return void
	 */
	public function updateCMSFields(&$fields) {
		$fields->addFieldsToTab("Root.Access", array(
			new HeaderField(_t('SiteTreeCMSWorkflow.APPROVEHEADER', "Who can approve requests inside the CMS?"), 2),
			$approveTypeField = new OptionsetField(
				"CanApproveType", 
				"",
				array(
					"Inherit" => _t('SiteTree.EDITINHERIT', "Inherit from parent page"),
					"LoggedInUsers" => _t('SiteTree.EDITANYONE', "Anyone who can log-in to the CMS"),
					"OnlyTheseUsers" => _t('SiteTree.EDITONLYTHESE', "Only these people (choose from list)")
				),
				"Inherit"
			),
			$approverGroupsField = new TreeMultiselectField("ApproverGroups", $this->owner->fieldLabel('ApproverGroups'))
		));
		
		$fields->addFieldsToTab("Root.Access", array(
			new HeaderField(_t('SiteTreeCMSWorkflow.PUBLISHAPPROVEDHEADER', "Who can publish approved requests inside the CMS?"), 2),
			$actionTypeField = new OptionsetField(
				"CanPublishType", 
				"",
				array(
					"Inherit" => _t('SiteTree.EDITINHERIT', "Inherit from parent page"),
					"LoggedInUsers" => _t('SiteTree.EDITANYONE', "Anyone who can log-in to the CMS"),
					"OnlyTheseUsers" => _t('SiteTree.EDITONLYTHESE', "Only these people (choose from list)")
				),
				"Inherit"
			),
			$actionerGroupsField = new TreeMultiselectField("PublisherGroups", $this->owner->fieldLabel('PublisherGroups'))
		));
		
		if(!$this->owner->canPublish() || !Permission::check('SITETREE_GRANT_ACCESS')) {
			$fields->replaceField('CanApproveType', $approveTypeField->performReadonlyTransformation());
			$fields->replaceField('ApproverGroups', $approverGroupsField->performReadonlyTransformation());
			$fields->replaceField('CanPublishType', $actionTypeField->performReadonlyTransformation());
			$fields->replaceField('PublisherGroups', $actionerGroupsField->performReadonlyTransformation());
		}
	}
	
	/**
	 * Returns a DataObjectSet of all the members that can approve this page
	 */
	public function ApproverMembers() {
		if($this->owner->CanApproveType == 'OnlyTheseUsers'){
			$groups = $this->owner->ApproverGroups();
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
		} elseif($this->owner->CanApproveType == 'Inherit') {
			if ($this->owner->ParentID) {
				return $this->owner->Parent()->ApproverMembers();
			} else { return SiteConfig::current_site_config()->ApproverMembers($member); }
		} elseif($this->owner->CanApproveType == 'LoggedInUsers') {
			return Permission::get_members_by_permission('CMS_ACCESS_CMSMain');
		} else {
			$group = Permission::get_groups_by_permission('ADMIN')->first();
			return $group->Members();
		}
	}
	
	/**
	 * This function should return true if the current user can approve requests
	 * for this page.
	 *
	 * @return boolean True if the current user can approve requests for this page.
	 */
	public function canApprove($member = null) {
		if(!$member && $member !== FALSE) $member = Member::currentUser();

		// check for admin permission
		if(Permission::checkMember($member, 'ADMIN')) return true;
		
		// check for workflow admin permission
		if(Permission::checkMember($member, 'IS_WORKFLOW_ADMIN')) return true;
		
		// check for missing cmsmain permission
		if(!Permission::checkMember($member, 'CMS_ACCESS_CMSMain')) return false;

		if ($this->canPublish($member)) return true;
		
		// check for empty spec
		if(!$this->owner->CanApproveType || $this->owner->CanApproveType == 'Anyone') return true;

		// check against parent page/site config
		if($this->owner->CanApproveType == 'Inherit') {
			if ($this->owner->Parent()->exists()) {
				// if (!$this->owner->Parent()->getExtensionInstance('SiteTreeCMSThreeStepWorkflow')->canApprove($member)) return false;
				if (!$this->owner->Parent()->canApprove($member)) return false;
			} else { return SiteConfig::current_site_config()->canApprove($member); }
		}
		
		// check for any logged-in users
		if($this->owner->CanApproveType == 'LoggedInUsers' && !Permission::checkMember($member, 'CMS_ACCESS_CMSMain')) return false;

		// check for specific groups
		if($this->owner->CanApproveType == 'OnlyTheseUsers' && (!$member || !$member->inGroups($this->owner->ApproverGroups()))) return false;

		return true;
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
		} elseif($this->owner->CanPublishType == 'Inherit') {
			if ($this->owner->Parent()->Exists()) {
				return $this->owner->Parent()->PublisherMembers();
			} else { return SiteConfig::current_site_config()->PublisherMembers($member); }
		} elseif($this->owner->CanPublishType == 'LoggedInUsers') {
			return Permission::get_members_by_permission('CMS_ACCESS_CMSMain');
		} else {
			$group = Permission::get_groups_by_permission('ADMIN')->first();
			return $group->Members();
		}
	}
	
	/**
	 * This function should return true if the current user can publish this
	 * page.
	 *
	 * @return boolean True if the current user can publish this page.
	 */
	public function canPublish($member = null) {
		if(!$member && $member !== FALSE) $member = Member::currentUser();

		// check for admin permission
		if(Permission::checkMember($member, 'ADMIN')) return true;
		
		// check for workflow admin permission
		if(Permission::checkMember($member, 'IS_WORKFLOW_ADMIN')) return true;
		
		// check for missing cmsmain permission
		if(!Permission::checkMember($member, 'CMS_ACCESS_CMSMain')) return false;

		// check for empty spec
		if(!$this->owner->CanPublishType || $this->owner->CanPublishType == 'Anyone') return true;

		// check against parent page/site config
		if($this->owner->CanPublishType == 'Inherit') {
			if ($this->owner->Parent()->exists()) {
				// if (!$this->owner->Parent()->getExtensionInstance('SiteTreeCMSTwoStepWorkflow')->canPublish($member)) return false;
				if (!$this->owner->Parent()->canPublish($member)) return false;
			} else { return SiteConfig::current_site_config()->canPublish($member); }
		}
		
		// check for any logged-in users
		if($this->owner->CanPublishType == 'LoggedInUsers' && !Permission::checkMember($member, 'CMS_ACCESS_CMSMain')) return false;

		// check for specific groups
		if($this->owner->CanPublishType == 'OnlyTheseUsers' && (!$member || !$member->inGroups($this->owner->PublisherGroups()))) return false;

		return true;
	}
	
	/**
	 * Make sure that a page has some peeps associated
	 *
	 * @return void
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
		
		if(!$this->owner->ApproverGroups()->Count()) {
			$SQL_group = Convert::raw2sql('site-content-approvers');
			$groupCheckObj = DataObject::get_one('Group', "Code = '{$SQL_group}'");
			if($groupCheckObj) $this->owner->ApproverGroups()->add($groupCheckObj);
		}
		
		if(!$this->owner->PublisherGroups()->Count()) {
			$SQL_group = Convert::raw2sql('site-content-publishers');
			$groupCheckObj = DataObject::get_one('Group', "Code = '{$SQL_group}'");
			if($groupCheckObj) $this->owner->PublisherGroups()->add($groupCheckObj);
		}
	}

	/**
	 * Setup the default groups
	 * 
	 * @return void
	 */
	function augmentDefaultRecords() {
		// For 2.3 and 2.4 compatibility
		$bt = defined('Database::USE_ANSI_SQL') ? "\"" : "`";
		
		$query = "SELECT * FROM {$bt}Group{$bt} WHERE {$bt}Group{$bt}.{$bt}Code{$bt} = 'site-content-authors'";
		if(!DB::query($query)->value()){
			$authorGroup = Object::create('Group');
			$authorGroup->Title = 'Site Content Authors';
			$authorGroup->Code = "site-content-authors";
			$authorGroup->write();
			Permission::grant($authorGroup->ID, "CMS_ACCESS_CMSMain");
			Permission::grant($authorGroup->ID, "CMS_ACCESS_AssetAdmin");
			Database::alteration_message("Added site content author group","created");
		}

		$query = "SELECT * FROM {$bt}Group{$bt} WHERE {$bt}Group{$bt}.{$bt}Code{$bt} = 'site-content-approvers'";
		if(!DB::query($query)->value()){
			$approversGroup = Object::create('Group');
			$approversGroup->Title = 'Site Content Approvers';
			$approversGroup->Code = "site-content-approvers";
			$approversGroup->write();
			Permission::grant($approversGroup->ID, "CMS_ACCESS_CMSMain");
			Permission::grant($approversGroup->ID, "CMS_ACCESS_AssetAdmin");
			Database::alteration_message("Added site content approver group","created");
		}
		
		$query = "SELECT * FROM {$bt}Group{$bt} WHERE {$bt}Group{$bt}.{$bt}Code{$bt} = 'site-content-publishers'";
		if(!DB::query($query)->value()){
			$actionersGroup = Object::create('Group');
			$actionersGroup->Title = 'Site Content Publishers';
			$actionersGroup->Code = "site-content-publishers";
			$actionersGroup->write();
			Permission::grant($actionersGroup->ID, "CMS_ACCESS_CMSMain");
			Permission::grant($actionersGroup->ID, "CMS_ACCESS_AssetAdmin");
			Database::alteration_message("Added site content publisher group","created");
		}
	}	
	
	function providePermissions() {
		return array(
			"IS_WORKFLOW_ADMIN" => "Is a CMS workflow admin",
		);
	}
}