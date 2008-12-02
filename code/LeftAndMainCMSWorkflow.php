<?php
class LeftAndMainCMSWorkflow extends LeftAndMainDecorator {
	
	public static $allowed_actions = array(
		'cms_requestpublication',
		'cms_requestdeletefromlive',
	);
	
	/**
	 * Handler for the CMS button
	 */
	public function cms_requestpublication($urlParams, $form) {
		$id = $urlParams['ID'];
		$record = DataObject::get_by_id("SiteTree", $id);
		$this->doRequestPublication($record);
		
		$members = $record->PublisherMembers();
		foreach($members as $member) {
			$emails[] = $member->Email;
		}
		$strEmails = implode(", ", $emails);
		
		FormResponse::status_message(
			sprintf(
				_t('SiteTreeCMSWorkflow.REQUEST_PUBLICATION_SUCCESS_MESSAGE','Emailed %s requesting publication'), 
				$strEmails
			), 
			'good'
		);
		return FormResponse::respond();	
	}
	
	public function doRequestPublication($record){
		$record->NeedsReview = true;
		$record->writeWithoutVersion();
		$currentUser = Member::CurrentUser();

		global $project;

		$members = $record->PublisherMembers();
		if($members->count()){
			foreach($members as $member){
				$notify = new PublishRequestEmail();
				$notify->setTo($member->Email);
				if($currentUser->Email) {
					$notify->setFrom($currentUser->Email);
				}else{
					$notify->setFrom(Email::getAdminEmail());
				}
				$notify->setSubject(
					_t(
						"SiteTreeCMSWorkflow.REQUEST_PUBLICATION_EMAIL_SUBJECT", 
						"Please review and publish the \"{$record->Title}\" page on your site."
					)
				);
				$emailData = array(
					"ProjectTitle" => strtoupper($project),
					"PageCMSLink" => "admin/show/".$record->ID,
					"Receiver" => $member,
					"Sender" => $currentUser,
					"Page" => $record,
					"StageSiteLink"	=> $record->Link()."?stage=stage",
					"LiveSiteLink"	=> $record->Link()."?stage=live",
					"DiffCMSLink" => $this->diffAdminLink($record)
				);
				$notify->populateTemplate($emailData);
				$notify->send();
			}
		}
		return $this;
	}
	
	public function cms_requestdeletefromlive($urlParams, $form) {
		$id = $urlParams['ID'];
		$record = DataObject::get_by_id("SiteTree", $id);
		$this->doRequestDeleteFromLive($record);
		
		$members = $record->PublisherMembers();
		foreach($members as $member) {
			$emails[] = $member->Email;
		}
		$strEmails = implode(", ", $emails);
		
		FormResponse::status_message(
			sprintf(
				_t('SiteTreeCMSWorkflow.REQUEST_DELETEFROMLIVE_SUCCESS_MESSAGE','Emailed %s requesting deletion'), 
				$strEmails
			), 
			'good'
		);
		return FormResponse::respond();	
	}
	
	public function doRequestDeleteFromLive($record){
		$record->NeedsReview = true;
		$record->writeWithoutVersion();
		$currentUser = Member::CurrentUser();

		global $project;

		$members = $record->PublisherMembers();
		if($members->count()){
			foreach($members as $member){
				$notify = new DeleteFromLiveRequestEmail();
				$notify->setTo($member->Email);
				if($currentUser->Email) {
					$notify->setFrom($currentUser->Email);
				}else{
					$notify->setFrom(Email::getAdminEmail());
				}
				$notify->setSubject(
					_t(
						"SiteTreeCMSWorkflow.REQUEST_DELETEFROMLIVE_EMAIL_SUBJECT", 
						"Please review and delete the \"{$record->Title}\" page on your site."
					)
				);
				$emailData = array(
					"ProjectTitle" => strtoupper($project),
					"PageCMSLink" => "admin/show/".$record->ID,
					"Receiver" => $member,
					"Sender" => $currentUser,
					"Page" => $record,
					"StageSiteLink"	=> $record->Link()."?stage=stage",
					"LiveSiteLink"	=> $record->Link()."?stage=live",
					"DiffCMSLink" => $this->diffAdminLink($record)
				);
				$notify->populateTemplate($emailData);
				$notify->send();
			}
		}
		return $this;
	}
	
	/**
	 * Returns a CMS link to see differences made in the request
	 * 
	 * @param Page $record
	 * @return string URL
	 */
	protected function diffAdminLink($record) {
		$fromVersion = $record->Version;
		$latestPublished = Versioned::get_one_by_stage($record->class, 'Live', "`SiteTree_Live`.ID = {$record->ID}", true, "Created DESC");
		if($latestPublished) $latestPublishedVersion = $latestPublished->Version;
		
		return "admin/compareversions/$record->ID/?From={$fromVersion}&To={$latestPublishedVersion}";
	}

}
?>