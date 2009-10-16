<?php

class ScheduledPublishing extends BuildTask {
	function getDescription() {
		return 'Publish changes that are scheduled';
	}
	function getTitle() {
		return 'CMS Workflow: embargo/expiry';
	}
	function run($httpRequest) {
		// Look for changes that need to be published
		$wfRequests = DataObject::get('WorkflowRequest', "Status = 'Scheduled' AND EmbargoDate <= '".SSDatetime::now()."'");
		if (count($wfRequests)) {
			foreach($wfRequests as $request) {
				// Use a try block to prevet one bad requests
				// taking down the whole queue
				try {
					echo "\n<br />Attempting to publish ".$request->Page()->Title.": ";
					$request->publish('Scheduled publishing', null, false);
					echo "ok.";
				} catch (Exception $e) {
					// Log it?
					echo "fail.";
					user_error("Error publishing change to Page ID ".$request->PageID." - ".$request->Page()->Title." Error: ".$e->getMessage, E_USER_WARNING);
					continue;
				}
			}
		}
		
		// Look for live pages that need to be expired
		$pagesToExpire = Versioned::get_by_stage('SiteTree', 'Live', "ExpiryDate <= '".SSDatetime::now()."'");
		if (count($pagesToExpire)) {
			foreach($pagesToExpire as $page) {
				// Use a try block to prevet one bad requests
				// taking down the whole queue
				try {
					echo "\n<br />Attempting to unpublish ".$page->Title.": ";
					
					// Close any existing workflows
					if ($wf = $page->openWorkflowRequest()) {
						echo "closing ".$wf->Status." workflow request: ";
						$wf->deny('Page automatically expired. Removing from Live site.');
						echo "ok, unpublishing: ";
					}

					$page->ExpiryDate = null;
					$page->write();
					$page->doUnpublish();
					
					echo "ok.";
				} catch (Exception $e) {
					// Log it?
					echo "fail.";
					user_error("Error unpublishing Page ID ".$page->ID." - ".$page->Title." Error: ".$e->getMessage, E_USER_WARNING);
					continue;
				}
			}
		}
	}
}