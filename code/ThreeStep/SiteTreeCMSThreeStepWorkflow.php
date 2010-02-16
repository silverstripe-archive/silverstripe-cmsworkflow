<?php

/**
 * Augment SiteTree with a new permissions, 'canApprove',
 * and 'canAction'.
 *
 * @package cmsworkflow
 * @subpackage ThreeStep
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
				"CanApproveType" => "Inherit",
				"CanPublishType" => "Inherit",
			)
		);
	}
	
	function getOpenRequest($workflowClass) {
		$wf = DataObject::get_one($workflowClass, "\"PageID\" = " . (int)$this->owner->ID . " AND \"Status\" NOT IN ('Completed', 'Denied', 'Cancelled')");
		if($wf) return $wf;
		
		return null;
	}
	
	function batchPublish() {
		$page = $this->owner;
		if($request = $page->openWorkflowRequest('WorkflowRequest')) {
			if ($request->Status == 'AwaitingApproval') {
				$result = $request->approve('Batch approval', null, false);
				if ($result) $result = $request->publish('Batch publish', null, false);
			}
			if ($request->Status == 'Approved') {
				$result = $request->publish('Batch publish', null, false);
			}
		} else { $result = false; }
		return $result;
	}
	function batchApprove() {
		$page = $this->owner;
		if($request = $page->openWorkflowRequest('WorkflowRequest')) {
			if ($request->Status == 'AwaitingApproval') {
				return $request->approve('Batch approval', null, false);
			}
		} else { return false; }
	}
	
	/**
	 * Returns true if a batch publication action can be triggered on this page
	 */
	function canBatchPublish($member = null) {
		$request = $this->owner->openWorkflowRequest();
		return $request && $request->Status == 'Approved' && $this->owner->canPublish($member);
	}

	/**
	 * Returns true if a batch approval action can be triggered on this page
	 */
	function canBatchApprove($member = null) {
		$request = $this->owner->openWorkflowRequest();
		return $request && $request->Status == 'AwaitingApproval' && $this->owner->canPublish($member);
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
			if($this->owner->CanApproveType == 'OnlyTheseUsers') {
				$fields->replaceField('ApproverGroups', $approverGroupsField->performReadonlyTransformation());
			} else {
				$fields->removeByName('ApproverGroups');
			}
			
			$fields->replaceField('CanPublishType', $actionTypeField->performReadonlyTransformation());
			if($this->owner->CanPublishType == 'OnlyTheseUsers') {
				$fields->replaceField('PublisherGroups', $actionerGroupsField->performReadonlyTransformation());
			} else {
				$fields->removeByName('PublisherGroups');
			}
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
			} else { return $this->owner->SiteConfig->ApproverMembers(); }
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
		if(!$member) $member = Member::currentUser();
		$memberID = $member->ID;
		
		if(isset(SiteTree::$cache_permissions['CanApproveType'][$this->owner->ID])) {
			return SiteTree::$cache_permissions['CanApproveType'][$this->owner->ID];
		}
		
		// DANGER, WILL ROBINSON!
		// we currently have not implemented extensions here. if you do
		// be aware that the WorkflowRequest::get_by_* functions use
		// batch_permission_check directly so you will need to ammend
		// them appropriately

		// check for (workflow)admin permission
		if(Permission::checkMember($member, array('ADMIN', 'IS_WORKFLOW_ADMIN'))) return true;
		
		if ($this->canPublish($member)) return true;
		
		$results = SiteTree::batch_permission_check(array($this->owner->ID), $memberID, 'CanApproveType', 'SiteTree_ApproverGroups', 'canApprove');
		return isset($results[$this->owner->ID]) ? $results[$this->owner->ID] : false;
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
			} else { return $this->owner->SiteConfig->PublisherMembers(); }
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
		if(!$member) $member = Member::currentUser();
		if (!$member) return false;
		if ($member instanceof Member) $memberID = $member->ID;
		else $memberID = $member;
		
		if(isset(SiteTree::$cache_permissions['CanPublishType'][$this->owner->ID])) {
			return SiteTree::$cache_permissions['CanPublishType'][$this->owner->ID];
		}
		
		// DANGER, WILL ROBINSON!
		// we currently have not implemented extensions here. if you do
		// be aware that the WorkflowRequest::get_by_* functions use
		// batch_permission_check directly so you will need to ammend
		// them appropriately

		// check for (workflow)admin permission
		if(Permission::checkMember($member, array('ADMIN', 'IS_WORKFLOW_ADMIN'))) return true;
		
		$results = SiteTree::batch_permission_check(array($this->owner->ID), $memberID, 'CanPublishType', 'SiteTree_PublisherGroups', 'canPublish');
		return isset($results[$this->owner->ID]) ? $results[$this->owner->ID] : false;
	}
	
	/**
	 * Make sure that a page has some peeps associated
	 *
	 * @return void
	 */
	function onAfterWrite() {
		if(!$this->owner->EditorGroups()->Count()) {
			$SQL_group = Convert::raw2sql('site-content-authors');
			$groupCheckObj = DataObject::get_one('Group', "\"Code\" = '{$SQL_group}'");
			if($groupCheckObj) $this->owner->EditorGroups()->add($groupCheckObj);
			
			$SQL_group = Convert::raw2sql('site-content-publishers');
			$groupCheckObj = DataObject::get_one('Group', "\"Code\" = '{$SQL_group}'");
			if($groupCheckObj) $this->owner->EditorGroups()->add($groupCheckObj);
		}
		
		if($this->owner->CanApproveType == 'OnlyTheseUsers' && !$this->owner->ApproverGroups()->Count()) {
			$SQL_group = Convert::raw2sql('site-content-approvers');
			$groupCheckObj = DataObject::get_one('Group', "\"Code\" = '{$SQL_group}'");
			if($groupCheckObj) $this->owner->ApproverGroups()->add($groupCheckObj);
		}
		
		if($this->owner->CanPublishType == 'OnlyTheseUsers' && !$this->owner->PublisherGroups()->Count()) {
			$SQL_group = Convert::raw2sql('site-content-publishers');
			$groupCheckObj = DataObject::get_one('Group', "\"Code\" = '{$SQL_group}'");
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
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		
		$query = "SELECT \"ID\" FROM {$bt}Group{$bt} WHERE {$bt}Group{$bt}.{$bt}Code{$bt} = 'site-content-authors'";
		if(!DB::query($query)->value()){
			$authorGroup = Object::create('Group');
			$authorGroup->Title = 'Site Content Authors';
			$authorGroup->Code = "site-content-authors";
			$authorGroup->write();
			Permission::grant($authorGroup->ID, "CMS_ACCESS_CMSMain");
			Permission::grant($authorGroup->ID, "CMS_ACCESS_AssetAdmin");
			if(method_exists('DB', 'alteration_message')) DB::alteration_message("Added site content author group","created");
		}

		$query = "SELECT \"ID\" FROM {$bt}Group{$bt} WHERE {$bt}Group{$bt}.{$bt}Code{$bt} = 'site-content-approvers'";
		if(!DB::query($query)->value()){
			$approversGroup = Object::create('Group');
			$approversGroup->Title = 'Site Content Approvers';
			$approversGroup->Code = "site-content-approvers";
			$approversGroup->write();
			Permission::grant($approversGroup->ID, "CMS_ACCESS_CMSMain");
			Permission::grant($approversGroup->ID, "CMS_ACCESS_AssetAdmin");

			if(method_exists('DB', 'alteration_message')) DB::alteration_message("Added site content approver group","created");
		}
		
		$query = "SELECT \"ID\" FROM {$bt}Group{$bt} WHERE {$bt}Group{$bt}.{$bt}Code{$bt} = 'site-content-publishers'";
		if(!DB::query($query)->value()){
			$actionersGroup = Object::create('Group');
			$actionersGroup->Title = 'Site Content Publishers';
			$actionersGroup->Code = "site-content-publishers";
			$actionersGroup->write();
			Permission::grant($actionersGroup->ID, "CMS_ACCESS_CMSMain");
			Permission::grant($actionersGroup->ID, "CMS_ACCESS_AssetAdmin");

			if(method_exists('DB', 'alteration_message')) DB::alteration_message("Added site content publisher group","created");
		}
	}	
	
	function providePermissions() {
		return array(
			"IS_WORKFLOW_ADMIN" => array(
				'name' => _t('SiteTreeCMSThreeStepWorkflow.PERM_WF_ADMIN', "Perform any workflow task"),
				'category' => _t('Permissions.PERMISSIONS_CATEGORY', 'Roles and access permissions'),
				'help' => _t('SiteTreeCMSThreeStepWorkflow.PERM_WF_ADMIN_HELP', 'Ability to do anything within workflow (approve, publish etc.), i.e., a global override of all workflow.'),
				'sort' => 500
			)
		);
	}
}
