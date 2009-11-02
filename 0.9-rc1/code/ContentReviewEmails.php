<?php

/**
 * Daily task to send emails to the owners of content items
 * when the review date rolls around
 *
 * @package cmsworkflow
 */
class ContentReviewEmails extends DailyTask {
	function run($req) { $this->process(); }
	function process() {
		// Disable subsite filter (if installed)
		if (ClassInfo::exists('Subsite')) {
			$oldSubsiteState = Subsite::$disable_subsite_filter;
			Subsite::$disable_subsite_filter = true;
		}
		
		$pages = DataObject::get('Page', "NextReviewDate = '".date('Y-m-d')."' AND OwnerID != 0");
		if ($pages && $pages->Count()) {
			foreach($pages as $page) {
				$owner = $page->Owner();
				if ($owner) {
					$sender = Security::findAnAdministrator();
					$recipient = $owner;

					$subject = sprintf(_t('ContentReviewEmails.SUBJECT', 'Page %s due for content review'), $page->Title);

					$email = new Email();
					$email->setTo($recipient->Email);
					$email->setFrom(($sender->Email) ? $sender->Email : Email::getAdminEmail());
					$email->setTemplate('ContentReviewEmails');
					$email->setSubject($subject);
					$email->populateTemplate(array(
						"PageCMSLink" => "admin/show/".$page->ID,
						"Recipient" => $recipient,
						"Sender" => $sender,
						"Page" => $page,
						"StageSiteLink"	=> $page->Link()."?stage=stage",
						"LiveSiteLink"	=> $page->Link()."?stage=live",
					));

					return $email->send();
				}
			}
		}
		
		// Revert subsite filter (if installed)
		if (ClassInfo::exists('Subsite')) {
			Subsite::$disable_subsite_filter = $oldSubsiteState;
		}
	}
}
