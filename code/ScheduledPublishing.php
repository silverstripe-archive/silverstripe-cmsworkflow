<?php

/**
 * Looks for pages that are embargo'd/expiry'd
 *
 * @package cmsworkflow
 */
class ScheduledPublishing extends BuildTask
{
    /**
     * Whether to echo progress
     */
    protected $suppressOutput = false;
    public function suppressOutput($yn = true)
    {
        $this->suppressOutput = $yn;
    }
    
    public function getDescription()
    {
        return 'Publish changes that are scheduled';
    }

    public function getTitle()
    {
        return 'CMS Workflow: embargo/expiry';
    }

    /**
     * Run the task, and do the business
     *
     * @param SS_HTTPRequest $httpRequest 
     */
    public function run($httpRequest)
    {
        require_once 'Zend/Log/Writer/Stream.php';
        SS_Log::add_writer(new Zend_Log_Writer_Stream('php://output'), SS_Log::NOTICE);
        
        $db = DB::getConn();
        if (method_exists($db, 'supportsLocks') && $db->supportsLocks() && !$db->getLock('ScheduledPublishing')) {
            $this->log('Publication has already been triggered by a different process');
            return;
        }
        
        Cookie::$report_errors = false;
        if (class_exists('Subsite')) {
            Subsite::$disable_subsite_filter = true;
        }

        if (class_exists('Subsite')) {
            Subsite::$disable_subsite_filter = true;
        }
        
        $this->log('Looking for changes that need to be published');
        $bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
        $wfRequests = DataObject::get('WorkflowRequest', "{$bt}Status{$bt} = 'Scheduled' AND {$bt}EmbargoDate{$bt} <= '".SS_Datetime::now()->getValue()."'");
        $this->log(sprintf('Found %d pages', $wfRequests ? count($wfRequests) : 0));
        $admin = Security::findAnAdministrator();
        $admin->logIn();
        
        
        if (count($wfRequests)) {
            foreach ($wfRequests as $request) {
                // Use a try block to prevent one bad request
                // taking down the whole queue
                try {
                    $page = $request->Page();
                    $this->log(sprintf("Attempting to publish '%s' (URL: %s)", $page->Title, $page->AbsoluteLink()));
                    // We remove the embargo date and republish to trigger this.
                    $request->EmbargoDate = null;
                    $result = $request->publish('Page was embargoed. Automatically published.', WorkflowSystemMember::get(), false);
                    $this->log(sprintf("Published '%s' (URL: %s)", $page->Title, $page->AbsoluteLink()));
                } catch (Exception $e) {
                    // Log it?
                    $this->log(sprintf("Failed to publish '%s (URL: %s)", $page->Title, $page->AbsoluteLink()));
                    user_error("Error publishing change to Page ID ".$request->PageID." - ".$request->Page()->Title." Error: ".$e->getMessage(), E_USER_WARNING);
                    continue;
                }
            }
        }
        
        $this->log('Looking for live pages that need to be expired');
        $pagesToExpire = Versioned::get_by_stage('SiteTree', 'Live', "\"ExpiryDate\" <= '".SS_Datetime::now()->getValue()."'");
        $this->log(sprintf('Found %d pages', $pagesToExpire ? count($pagesToExpire) : 0));
        if (count($pagesToExpire)) {
            foreach ($pagesToExpire as $page) {
                // Use a try block to prevent one bad request
                // taking down the whole queue
                try {
                    $this->log(sprintf("Attempting to unpublish '%s' (URL: %s)", $page->Title, $page->AbsoluteLink()));
                    
                    // Close any existing workflows
                    if ($wf = $page->openWorkflowRequest()) {
                        $this->log(sprintf("Closing '%s' workflow request for '%s'", $wf->Status, $page->Title));
                        $wf->deny('Page automatically expired. Removing from Live site.', $admin);
                    }

                    $page->ExpiryDate = null;
                    $page->write();
                    $page->doUnpublish();
                    
                    $this->log(sprintf("Unpublished '%s' (URL: %s)", $page->Title, $page->AbsoluteLink()));
                } catch (Exception $e) {
                    $this->log(sprintf("Failed to unpublish '%s' (URL: %s)", $page->Title, $page->AbsoluteLink()));
                    user_error("Error unpublishing Page ID ".$page->ID." - ".$page->Title." Error: ".$e->getMessage(), E_USER_WARNING);
                    continue;
                }
            }
        }
        
        // We don't need to clear the lock on every potential exception,
        // as the closing of the DB connection will do that for us.
        if (method_exists($db, 'supportsLocks') && $db->supportsLocks()) {
            $db->releaseLock('ScheduledPublishing');
        }
    }
    
    /**
     * @param String
     */
    protected function log($message)
    {
        if (!$this->suppressOutput) {
            // TODO Only works because we're using a Zend writer rather that
            // SS_Log* writers, which assume the argument to be an array or exception, breaking the base API.
            SS_Log::log($message, SS_Log::NOTICE);
        }
    }
}
