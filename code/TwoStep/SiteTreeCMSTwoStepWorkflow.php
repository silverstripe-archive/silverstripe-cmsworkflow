<?php

/**
 * Override SiteTree's permission, 'canPublish'
 *
 * @package cmsworkflow
 * @subpackage twostep
 * @author Tom Rix
 */
class SiteTreeCMSTwoStepWorkflow extends SiteTreeCMSWFDecorator {
	public function extraStatics() {
		return array(
			'db' => array(
				"CanPublishType" =>"Enum('LoggedInUsers, OnlyTheseUsers, Inherit', 'Inherit')"
			),
			'many_many' => array(
				"PublisherGroups" => "Group",
			),
			'defaults' => array(
				"CanPublishType" => "OnlyTheseUsers",
			)
		);
	}
	
	public function getOpenRequest($workflowClass) {
		$wf = DataObject::get_one($workflowClass, "PageID = " . (int)$this->owner->ID . " AND Status NOT IN ('Completed', 'Denied', 'Cancelled')");
		if($wf) return $wf;
		
		return null;
	}
	
	/**
	 * Implement permissions for TwoStep
	 *
	 * @return void
	 */
	public function updateCMSFields(&$fields) {
		$fields->addFieldsToTab("Root.Access", array(
			new HeaderField(_t('SiteTreeCMSWorkflow.PUBLISHHEADER', "Who can publish this inside the CMS?"), 2),
			$publishTypeField = new OptionsetField(
				"CanPublishType", 
				"",
				array(
					"Inherit" => _t('SiteTree.EDITINHERIT', "Inherit from parent page"),
					"LoggedInUsers" => _t('SiteTree.EDITANYONE', "Anyone who can log-in to the CMS"),
					"OnlyTheseUsers" => _t('SiteTree.EDITONLYTHESE', "Only these people (choose from list)")
				),
				"Inherit"
			),
			$publisherGroupsField = new TreeMultiselectField("PublisherGroups", $this->owner->fieldLabel('PublisherGroups'))
		));
		if(!$this->owner->canPublish() || !Permission::check('SITETREE_GRANT_ACCESS')) {
			$fields->replaceField('CanPublishType', $publishTypeField->performReadonlyTransformation());
			$fields->replaceField('PublisherGroups', $publisherGroupsField->performReadonlyTransformation());
		}
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
			} else { return new DataObjectSet(); }
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
		
		// check for missing cmsmain permission
		if(!Permission::checkMember($member, 'CMS_ACCESS_CMSMain')) return false;

		// check for empty spec
		if(!$this->owner->CanPublishType || $this->owner->CanPublishType == 'Anyone') return true;

		// check against parent page (default to FALSE if there is no parent page)
		if($this->owner->CanPublishType == 'Inherit') {
			if ($this->owner->Parent()->exists()) {
				if (!$this->owner->Parent()->getExtensionInstance('SiteTreeCMSTwoStepWorkflow')->canPublish($member)) return false;
			} else { return false; }
		}
		
		// check for any logged-in users
		if($this->owner->CanPublishType == 'LoggedInUsers' && !Permission::checkMember($member, 'CMS_ACCESS_CMSMain')) return false;

		// check for specific groups
		if($this->owner->CanPublishType == 'OnlyTheseUsers' && (!$member || !$member->inGroups($this->owner->PublisherGroups()))) return false;

		return true;
	}
	
	function whoCanApprove() {
		return $this->PublisherMembers();
	}
	
	function canDenyRequests() {
		return $this->canPublish();
	}
	
	function canRequestEdit() {
		return $this->canPublish();
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
		// For 2.3 and 2.4 compatibility
		$bt = defined('SS_SS_Database::USE_ANSI_SQL') ? "\"" : "`";
		
		$query = "SELECT * FROM {$bt}Group{$bt} WHERE {$bt}Group{$bt}.{$bt}Code{$bt} = 'site-content-authors'";
		if(!DB::query($query)->value()){
			$authorGroup = Object::create('Group');
			$authorGroup->Title = 'Site Content Authors';
			$authorGroup->Code = "site-content-authors";
			$authorGroup->write();
			Permission::grant($authorGroup->ID, "CMS_ACCESS_CMSMain");
			Permission::grant($authorGroup->ID, "CMS_ACCESS_AssetAdmin");
			SS_SS_Database::alteration_message("Added site content author group","created");
		}

		$query = "SELECT * FROM {$bt}Group{$bt} WHERE {$bt}Group{$bt}.{$bt}Code{$bt} = 'site-content-publishers'";
		if(!DB::query($query)->value()){
			$publishersGroup = Object::create('Group');
			$publishersGroup->Title = 'Site Content Publishers';
			$publishersGroup->Code = "site-content-publishers";
			$publishersGroup->write();
			Permission::grant($publishersGroup->ID, "CMS_ACCESS_CMSMain");
			Permission::grant($publishersGroup->ID, "CMS_ACCESS_AssetAdmin");
			SS_SS_Database::alteration_message("Added site content publisher group","created");
		}
	}
}