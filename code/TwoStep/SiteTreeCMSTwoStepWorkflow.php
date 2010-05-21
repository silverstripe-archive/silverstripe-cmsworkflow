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
			)
		);
	}
	
	public function getOpenRequest($workflowClass) {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";

		$wf = DataObject::get_one($workflowClass, "{$bt}PageID{$bt} = " . (int)$this->owner->ID . " AND {$bt}Status{$bt} NOT IN ('Completed', 'Denied', 'Cancelled')");
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
			} else { return SiteConfig::current_site_config()->PublisherMembers(); }
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
	public function canPublish($member = null, $dbg = false) {
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
	 * Stub. Hook into canPublish() for template usage
	 */
	function canApprove($member = null) {
		return $this->canPublish($member);
	}
	
	function whoCanApprove() {
		return $this->PublisherMembers();
	}
	
	function canDenyRequests($member = null) {
		return $this->canPublish($member);
	}
	
	function canRequestEdit($member = null) {
		return $this->canPublish($member);
	}
	
	/**
	 * Adds mappings of the default groups created.
	 * @todo Also re-adds default groups if all existing custom groups
	 * are deselected from a record - is this desired behaviour?
	 */
	function onAfterWrite() {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";

		if(!$this->owner->EditorGroups()->Count()) {
			$SQL_group = Convert::raw2sql('site-content-authors');
			$groupCheckObj = DataObject::get_one('Group', "{$bt}Group{$bt}.{$bt}Code{$bt} = '{$SQL_group}'");
			if($groupCheckObj) $this->owner->EditorGroups()->add($groupCheckObj);
			
			$SQL_group = Convert::raw2sql('site-content-publishers');
			$groupCheckObj = DataObject::get_one('Group', "{$bt}Group{$bt}.{$bt}Code{$bt} = '{$SQL_group}'");
			if($groupCheckObj) $this->owner->EditorGroups()->add($groupCheckObj);
		}
		
		if(!$this->owner->PublisherGroups()->Count()) {
			$SQL_group = Convert::raw2sql('site-content-publishers');
			$groupCheckObj = DataObject::get_one('Group', "{$bt}Group{$bt}.{$bt}Code{$bt} = '{$SQL_group}'");
			if($groupCheckObj) $this->owner->PublisherGroups()->add($groupCheckObj);
		}

	}

	function augmentDefaultRecords() {
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

		$query = "SELECT \"ID\" FROM {$bt}Group{$bt} WHERE {$bt}Group{$bt}.{$bt}Code{$bt} = 'site-content-publishers'";
		if(!DB::query($query)->value()){
			$publishersGroup = Object::create('Group');
			$publishersGroup->Title = 'Site Content Publishers';
			$publishersGroup->Code = "site-content-publishers";
			$publishersGroup->write();
			Permission::grant($publishersGroup->ID, "CMS_ACCESS_CMSMain");
			Permission::grant($publishersGroup->ID, "CMS_ACCESS_AssetAdmin");

			if(method_exists('DB', 'alteration_message')) DB::alteration_message("Added site content publisher group","created");
		}
	}
}
