<?php

class SiteConfigThreeStepWorkflow extends DataObjectDecorator {
	public function extraStatics() {
		return array(
			'db' => array(
				"CanApproveType" =>"Enum('LoggedInUsers, OnlyTheseUsers', 'OnlyTheseUsers')",
				"CanPublishType" =>"Enum('LoggedInUsers, OnlyTheseUsers', 'OnlyTheseUsers')"
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
	
	/**
	 * Update SiteConfig with the top level fields
	 *
	 * @param FieldSet $fields 
	 * @return void
	 */
	function updateEditFormFields(&$fields) {
		$fields->addFieldsToTab("Root.Access", array(
			new HeaderField(_t('SiteTreeCMSWorkflow.APPROVEHEADER', "Who can approve requests inside the CMS?"), 2),
			$approveTypeField = new OptionsetField(
				"CanApproveType", 
				"",
				array(
					"LoggedInUsers" => _t('SiteTree.EDITANYONE', "Anyone who can log-in to the CMS"),
					"OnlyTheseUsers" => _t('SiteTree.EDITONLYTHESE', "Only these people (choose from list)")
				),
				"OnlyTheseUsers"
			),
			$approverGroupsField = new TreeMultiselectField("ApproverGroups", "Approver groups")
		));
		
		$fields->addFieldsToTab("Root.Access", array(
			new HeaderField(_t('SiteTreeCMSWorkflow.PUBLISHAPPROVEDHEADER', "Who can publish approved requests inside the CMS?"), 2),
			$actionTypeField = new OptionsetField(
				"CanPublishType", 
				"",
				array(
					"LoggedInUsers" => _t('SiteTree.EDITANYONE', "Anyone who can log-in to the CMS"),
					"OnlyTheseUsers" => _t('SiteTree.EDITONLYTHESE', "Only these people (choose from list)")
				),
				"OnlyTheseUsers"
			),
			$actionerGroupsField = new TreeMultiselectField("PublisherGroups", "Publisher groups")
		));
		
		if(!Permission::check('EDIT_SITECONFIG')) {
			$fields->replaceField('CanApproveType', $approveTypeField->performReadonlyTransformation());
			$fields->replaceField('ApproverGroups', $approverGroupsField->performReadonlyTransformation());
			$fields->replaceField('CanPublishType', $actionTypeField->performReadonlyTransformation());
			$fields->replaceField('PublisherGroups', $actionerGroupsField->performReadonlyTransformation());
		}
	}
	
	/**
	 * Returns a DataObjectSet of all the members that can approve pages
	 * on this site by default
	 */
	public function ApproverMembers() {
		if ($this->owner->CanApproveType == 'OnlyTheseUsers') {
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
		} elseif($this->owner->CanApproveType == 'LoggedInUsers') {
			return Permission::get_members_by_permission('CMS_ACCESS_CMSMain');
		} else {
			$group = Permission::get_groups_by_permission('ADMIN')->first();
			return $group->Members();
		}
	}
	
	/**
	 * Returns a DataObjectSet of all the members that can publish pages
	 * on this site by default
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
		} elseif($this->owner->CanPublishType == 'LoggedInUsers') {
			return Permission::get_members_by_permission('CMS_ACCESS_CMSMain');
		} else {
			$group = Permission::get_groups_by_permission('ADMIN')->first();
			return $group->Members();
		}
	}
	
	/**
	 * This function should return true if the current user can publish pages
	 * on this site by default
	 *
	 * @return boolean
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

		// check for any logged-in users
		if($this->owner->CanPublishType == 'LoggedInUsers' && !Permission::checkMember($member, 'CMS_ACCESS_CMSMain')) return false;

		// check for specific groups
		if($this->owner->CanPublishType == 'OnlyTheseUsers' && (!$member || !$member->inGroups($this->owner->PublisherGroups()))) return false;

		return true;
	}
	
	/**
	 * This function should return true if the current user can approve pages
	 * on this site by default
	 *
	 * @return boolean
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
		
		// check for any logged-in users
		if($this->owner->CanApproveType == 'LoggedInUsers' && !Permission::checkMember($member, 'CMS_ACCESS_CMSMain')) return false;

		// check for specific groups
		if($this->owner->CanApproveType == 'OnlyTheseUsers' && (!$member || !$member->inGroups($this->owner->ApproverGroups()))) return false;

		return true;
	}
	
	/**
	 * Make sure that a page has some peeps associated
	 *
	 * @return void
	 */
	function onAfterWrite() {
		if(!$this->owner->ApproverGroups()->Count() && $this->owner->CanApproveType == 'OnlyTheseUsers') {
			$SQL_group = Convert::raw2sql('site-content-approvers');
			$groupCheckObj = DataObject::get_one('Group', "\"Code\" = '{$SQL_group}'");
			if($groupCheckObj) $this->owner->ApproverGroups()->add($groupCheckObj);
		}
		
		if(!$this->owner->PublisherGroups()->Count() && $this->owner->CanPublishType == 'OnlyTheseUsers') {
			$SQL_group = Convert::raw2sql('site-content-publishers');
			$groupCheckObj = DataObject::get_one('Group', "\"Code\" = '{$SQL_group}'");
			if($groupCheckObj) $this->owner->PublisherGroups()->add($groupCheckObj);
		}
	}
}
