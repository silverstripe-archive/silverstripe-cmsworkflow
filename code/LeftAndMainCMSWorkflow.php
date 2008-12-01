<?php
class LeftAndMainCMSWorkflow extends LeftAndMainDecorator {
	
	public static $allowed_actions = array(
		'cms_requestpublication'
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
					"Page" => $this,
					"StageSiteLink"	=> $record->Link()."?stage=stage",
					"LiveSiteLink"	=> $record->Link()."?stage=live",
				);
				$notify->populateTemplate($emailData);
				$notify->send();
			}
		}
		return $this;
	}
}
?>