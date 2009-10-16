<?php

class ScheduledPublishing extends BuildTask {
	protected $suppressOutput = false;
	
	function getDescription() {
		return 'Publish changes that are scheduled';
	}
	function getTitle() {
		return 'CMS Workflow: embargo/expiry';
	}
	function suppressOutput($yn = true) {
		$this->suppressOutput = $yn;
	}
	function run($httpRequest) {
		// Look for changes that need to be published
		$wfRequests = DataObject::get('WorkflowRequest', "Status = 'Scheduled' AND EmbargoDate <= '".SSDatetime::now()->getValue()."'");
		$admin = Security::findAnAdministrator();
		$admin->logIn();
		
		if (count($wfRequests)) {
			foreach($wfRequests as $request) {
				// Use a try block to prevent one bad request
				// taking down the whole queue
				try {
					if (!$this->suppressOutput) echo "\n<br />Attempting to publish ".$request->Page()->Title.": ";
					$request->publish('Page was embargoed. Automatically published.', $admin, false);
					if (!$this->suppressOutput) echo "ok.";
				} catch (Exception $e) {
					// Log it?
					if (!$this->suppressOutput) echo "fail.";
					user_error("Error publishing change to Page ID ".$request->PageID." - ".$request->Page()->Title." Error: ".$e->getMessage(), E_USER_WARNING);
					continue;
				}
			}
		}
		
		// Look for live pages that need to be expired
		$pagesToExpire = Versioned::get_by_stage('SiteTree', 'Live', "ExpiryDate <= '".SSDatetime::now()->getValue()."'");
		if (count($pagesToExpire)) {
			foreach($pagesToExpire as $page) {
				// Use a try block to prevent one bad request
				// taking down the whole queue
				try {
					if (!$this->suppressOutput) echo "\n<br />Attempting to unpublish ".$page->Title.": ";
					
					// Close any existing workflows
					if ($wf = $page->openWorkflowRequest()) {
						if (!$this->suppressOutput) echo "closing ".$wf->Status." workflow request: ";
						$wf->deny('Page automatically expired. Removing from Live site.', $admin);
						if (!$this->suppressOutput) echo "ok, unpublishing: ";
					}

					$page->ExpiryDate = null;
					$page->write();
					$page->doUnpublish();
					
					if (!$this->suppressOutput) echo "ok.";
				} catch (Exception $e) {
					// Log it?
					if (!$this->suppressOutput) echo "fail.";
					user_error("Error unpublishing Page ID ".$page->ID." - ".$page->Title." Error: ".$e->getMessage(), E_USER_WARNING);
					continue;
				}
			}
		}
	}
}