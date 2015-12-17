<?php
/**
 * Tracks the history of a {@link WorkflowRequest} object.
 * A new object is created whenever the Status property of a request
 * changes, including the author and the current page version of the draft site.
 * 
 * @package cmsworkflow
 */
class WorkflowRequestChange extends DataObject
{
    public static $db = array(
        'Status' => 'Varchar', // @see WorkflowRequest->Status
        'PageDraftVersion' => 'Int', // version of the page at draft stage
        'PageLiveVersion' => 'Int', // version of the page at live/published stage
        'Comment' => 'Text',
    );
    
    public static $has_one = array(
        'Author' => 'Member',
        'WorkflowRequest' => 'WorkflowRequest',
    );
    
    public static $summary_fields = array(
        'Status',
        'Author.Title',
        'Comment',
    );
    
    public static $sort = "\"Created\" ASC";
    
    public static $casting = array(
        'StatusDescription' => 'Varchar',
        'DiffLinkToPrevious' => 'Varchar',
        'DiffLinkToOriginalRequest' => 'Varchar',
        'DiffLinkOriginalToLastPublished' => 'Varchar',
        'DiffLinkToLastPublished' => 'Varchar'
    );
    
    public static $default_sort = "\"ID\" ASC";
    
    /**
     * @return WorkflowRequestChange
     */
    public function PreviousChange()
    {
        //$createdDate = $this->obj('Created')->Format();
        $changes = $this->WorkflowRequest()->Changes("\"Created\" < '{$this->Created}'", "\"Created\" DESC", null, 1);
        return ($changes) ? $changes->First() : null;
    }
    
    /**
     * @return WorkflowRequestChange
     */
    public function NextChange()
    {
        $changes = $this->WorkflowRequest()->Changes("\"Created\" > '{$this->Created}'", "\"Created\" DESC", null, 1);
        return ($changes) ? $changes->First() : null;
    }
    
    /**
     * Compares the changes made in this specific change object
     * with the previous change (if existing).
     * 
     * @return string Relative URL into the CMS
     */
    public function getDiffLinkContentToPrevious()
    {
        $link = $this->getDiffLinkToPrevious();

        if (!$link) {
            return 'Not applicable';
        }
        
        return "<a href=\"$link\" target=\"_blank\" class=\"externallink\">Show</a>";
    }
    
    public function getDiffLinkToPrevious()
    {
        $page = $this->WorkflowRequest()->Page();
        $previousChange = $this->PreviousChange();
        $fromVersion = ($previousChange) ? $previousChange->PageDraftVersion : $this->WorkflowRequest()->Changes()->First()->PageLiveVersion;
        $toVersion = $this->PageDraftVersion;
        
        if ($fromVersion == 0) {
            return false;
        }

        $link = "admin/compareversions/$page->ID/?From={$fromVersion}&To={$toVersion}";
        if (Object::has_extension('SiteTree', 'Translatable')) {
            $link .= '&locale=' . Translatable::get_current_locale();
        }
        
        return $link;
    }
    
    /**
     * Compares the changes made on draft since the request was first lodged.
     * 
     * @return string Relative URL into the CMS
     */
    public function getDiffLinkToOriginalRequest()
    {
        $page = $this->WorkflowRequest()->Page();
        $firstChange = $this->WorkflowRequest()->Changes()->First();
        if (!$firstChange) {
            return false;
        }
        $fromVersion = $firstChange->PageDraftVersion;
        $toVersion = $this->PageDraftVersion;
        
        $link = "admin/compareversions/$page->ID/?From={$fromVersion}&To={$toVersion}";
        if (Object::has_extension('SiteTree', 'Translatable')) {
            $link .= '&locale=' . Translatable::get_current_locale();
        }
        
        return $link;
    }
    
    /**
     * Compares the original change request with the currently
     * published version. Not specific to this change object.
     * 
     * @return string Relative URL into the CMS
     */
    public function getDiffLinkOriginalToLastPublished()
    {
        // For 2.3 and 2.4 compatibility
        $bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";

        $page = $this->WorkflowRequest()->Page();
        $fromVersion = $page->Version;
        $latestPublished = Versioned::get_one_by_stage($page->class, 'Live', "{$bt}SiteTree_Live{$bt}.{$bt}ID{$bt} = {$page->ID}", true, "\"Created\" DESC");
        if (!$latestPublished) {
            return false;
        }
        $toVersion = $latestPublished->Version;
        
        $link = "admin/compareversions/$page->ID/?From={$fromVersion}&To={$toVersion}";
        if (Object::has_extension('SiteTree', 'Translatable')) {
            $link .= '&locale=' . Translatable::get_current_locale();
        }

        return $link;
    }
    
    /**
     * Compares the current change to the last published version.
     * 
     * @return string Relative URL into the CMS
     */
    public function getDiffLinkToLastPublished()
    {
        // For 2.3 and 2.4 compatibility
        $bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";

        $page = $this->WorkflowRequest()->Page();
        $fromVersion = $this->PageDraftVersion;
        $latestPublished = Versioned::get_one_by_stage($page->class, 'Live', "{$bt}SiteTree_Live{$bt}.{$bt}ID{$bt} = {$page->ID}", true, "\"Created\" DESC");
        if (!$latestPublished) {
            return false;
        }
        $toVersion = $latestPublished->Version;
        
        $link = "admin/compareversions/$page->ID/?From={$fromVersion}&To={$toVersion}";
        if (Object::has_extension('SiteTree', 'Translatable')) {
            $link .= '&locale=' . Translatable::get_current_locale();
        }

        return $link;
    }
    
    /**
     * Gets the $Status property as a translated natural language value.
     * 
     * @return string
     */
    public function getStatusDescription()
    {
        return WorkflowRequest::get_status_description($this->Status);
    }
}
