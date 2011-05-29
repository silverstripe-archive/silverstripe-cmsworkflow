<?php

/**
 * Looks for pages that are embargo'd/expiry'd
 *
 * @package cmsworkflow
 */
class ScheduledPublishing extends BuildTask {
	/**
	 * Whether to echo progress
	 */
	protected $suppressOutput = false;
	function suppressOutput($yn = true) {
		$this->suppressOutput = $yn;
	}
	
	function getDescription() {
		return 'Publish changes that are scheduled';
	}
	function getTitle() {
		return 'CMS Workflow: embargo/expiry';
	}

	/**
	 * Run the task, and do the business
	 *
	 * @param SS_HTTPRequest $httpRequest 
	 */
	function run($httpRequest) {
		Cookie::$report_errors = false;
		if (class_exists('Subsite')) Subsite::$disable_subsite_filter = true;

		if (class_exists('Subsite')) Subsite::$disable_subsite_filter = true;
		
		// Look for changes that need to be published
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$wfRequests = DataObject::get('WorkflowRequest', "{$bt}Status{$bt} = 'Scheduled' AND {$bt}EmbargoDate{$bt} <= '".SS_Datetime::now()->getValue()."'");
		$admin = Security::findAnAdministrator();
		$admin->logIn();
		
		
		if (count($wfRequests)) {
			foreach($wfRequests as $request) {
				// Use a try block to prevent one bad request
				// taking down the whole queue
				try {
					if (!$this->suppressOutput) echo "\n<br />Attempting to publish ".$request->Page()->Title.": ";
					// We remove the embargo date and republish to trigger this.
					$request->EmbargoDate = null;
					$result = $request->publish('Page was embargoed. Automatically published.', WorkflowSystemMember::get(), false);
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
		$pagesToExpire = Versioned::get_by_stage('SiteTree', 'Live', "\"ExpiryDate\" <= '".SS_Datetime::now()->getValue()."'");
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