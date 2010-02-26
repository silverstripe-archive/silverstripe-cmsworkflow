<?php

class SiteConfigTwoStepWorkflow extends DataObjectDecorator {
	public function extraStatics() {
		return array(
			'db' => array(
				"CanPublishType" =>"Enum('LoggedInUsers, OnlyTheseUsers', 'OnlyTheseUsers')"
			),
			'many_many' => array(
				"PublisherGroups" => "Group",
			),
			'defaults' => array(
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
			new HeaderField(_t('SiteConfigCMSWorkflow.PUBLISHAPPROVEDHEADER', "Who can publish requests inside the CMS?"), 2),
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
		
		if(!Permission::check('ADMIN')) {
			$fields->replaceField('CanPublishType', $actionTypeField->performReadonlyTransformation());
			$fields->replaceField('PublisherGroups', $actionerGroupsField->performReadonlyTransformation());
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
		} else if ($this->owner->CanPublishType == 'LoggedInUsers') {
			// We don't want to return every user in the CMS....
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
		if (is_numeric($member)) $member = DataObject::get_by_id('Member', $member);

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
	 * Make sure that a page has some peeps associated
	 *
	 * @return void
	 */
	function onAfterWrite() {
		if(!$this->owner->PublisherGroups()->Count() && $this->owner->CanPublishType == 'OnlyTheseUsers') {
			$SQL_group = Convert::raw2sql('site-content-publishers');
			$groupCheckObj = DataObject::get_one('Group', "\"Code\" = '{$SQL_group}'");
			if($groupCheckObj) $this->owner->PublisherGroups()->add($groupCheckObj);
		}
	}
}
