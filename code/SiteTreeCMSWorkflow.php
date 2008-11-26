<?php
/**
 * Extension to SiteTree for CMS Workflow support.
 * 
 * Creates
 *
 * @package cmsworkflow
 */
class SiteTreeCMSWorkflow extends DataObjectDecorator {
	function extraDBFields() {
		return array(
			'db' => array(
				"NeedsPublication" => "Boolean",
				"CanPublishType" =>"Enum('LoggedInUsers, OnlyTheseUsers', 'OnlyTheseUsers')", 
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
		if($this->owner->canPublish()) {
			$fields->addFieldsToTab("Root.Access", array(
				new HeaderField(_t('SiteTreeCMSWorkflow.PUBLISHHEADER', "Who can publish this inside the CMS?"), 2),
				new OptionsetField(
					"CanPublishType", 
					"",
					array(
						"LoggedInUsers" => _t('SiteTree.EDITANYONE', "Anyone who can log-in to the CMS"),
						"OnlyTheseUsers" => _t('SiteTree.EDITONLYTHESE', "Only these people (choose from list)")
					),
					"OnlyTheseUsers"
				),
				new TreeMultiselectField("PublisherGroups", $this->owner->fieldLabel('PublisherGroups'))
			));
			
		} else {
			$fields->removeFieldFromTab("Root", "Access");
		}
	}
	
	public function updateCMSActions(&$actions) {
		if(!$this->owner->canPublish()) {
			foreach($actions as $i => $action) if($action->Name() == 'publish') unset($actions[$i]);
			if($this->owner->canEdit() && $this->owner->stagesDiffer('Stage', 'Live')) { 
				$actions[] = new FormAction('callPageMethod', _t('SiteTreeCMSWorkflow.BUTTONREQUESTPUBLICATION', 'Request Publication'), null, 'cms_requestpublication');
			}
		}
	}
	
	/**
	 * Handler for the CMS button
	 */
	public function cms_requestpublication() {
		$this->doRequestPublication();
		
		$members = $this->owner->PublisherMembers();
		foreach($members as $member) {
			$emails[] = $member->Email;
		}
		$strEmails = implode(", ", $emails);
		
		FormResponse::status_message(
			sprintf(_t('SiteTreeCMSWorkflow.REQUEST_PUBLICATION_SUCCESS_MESSAGE','Emailed %s requesting publication'), 
			$strEmails), 'good');
		return FormResponse::respond();	
	}
	
	/**
	 * Returns a DataObjectSet of all the members that can publish this page
	 */
	public function PublisherMembers() {
		if($this->owner->CanPublisherType == 'OnlyTheseUsers'){
			$groups = $this->owner->PublisherGroups();
			$members = new DataObjectSet();
			foreach($groups as $group) {
				$members->merge($groups->Members());
			}
			return $members;
		} else {
			$group = Permission::get_groups_by_permission('ADMIN')->first();
			return $group->Members();
		}
	}

	public function doRequestPublication(){
		$this->owner->NeedsPublication = true;
		$this->owner->writeWithoutVersion();
		$currentUser = Member::CurrentUser();
		global $project;

		$members = $this->PublisherMembers();
		if($members->count()){
			foreach($members as $member){
				$notify = new PublishRequestEmail();
				$notify -> setTo($member->Email);
				if($currentUser->Email) {
					$notify -> setFrom($currentUser->Email);
				}else{
					$notify -> setFrom(Email::getAdminEmail());
				}
				$notify -> setSubject(_t("SiteTreeCMSWorkflow.REQUEST_PUBLICATION_EMAIL_SUBJECT", "Please review and publish the \"{$this->owner->Title}\" page on your site."));
				$emailData = array(
					"ProjectTitle" => strtoupper($project),
					"PageCMSLink" => "admin/show/".$this->owner->ID,
					"Receiver" => $member,
					"Sender" => $currentUser,
					"Page" => $this,
					"StageSiteLink"	=> $this->owner->Link()."?stage=stage",
					"LiveSiteLink"	=> $this->owner->Link()."?stage=live",
				);
				$notify->populateTemplate($emailData);
				$notify->send();
			}
		}
		return $this;
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
	 * Adds mappings of the default groups created 
	 */
	function onAfterWrite() {
		if(!$this->owner->EditorGroups()->Count()) {
			$SQL_group = Convert::raw2sql('site-content-authors');
	        $groupCheckObj = DataObject::get_one('Group', "Code = '{$SQL_group}'");
			//Debug::show(DataObject::get('Group'));
	        $this->owner->EditorGroups()->add($groupCheckObj);
			
			$SQL_group = Convert::raw2sql('site-content-publishers');
			$groupCheckObj = DataObject::get_one('Group', "Code = '{$SQL_group}'");
			$this->owner->EditorGroups()->add($groupCheckObj);
		}
		
		if(!$this->owner->PublisherGroups()->Count()) {
			$SQL_group = Convert::raw2sql('site-content-publishers');
			$groupCheckObj = DataObject::get_one('Group', "Code = '{$SQL_group}'");
			$this->owner->PublisherGroups()->add($groupCheckObj);
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
		$this->owner->NeedsPublication = false;
		$this->owner->writeWithoutVersion();
	}
	
}
?>