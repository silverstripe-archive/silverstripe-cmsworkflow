<?php

/**
 * Filter the SiteTree by pages awaiting approval
 *
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class CMSWorkflowThreeStepFilters_PagesAwaitingApproval extends CMSSiteTreeFilter
{
    public static function title()
    {
        return _t('CMSWorkflowThreeStepFilters_PagesAwaitingApproval.TITLE', "Pages awaiting approval");
    }

    public function pagesIncluded()
    {
        return DB::query('SELECT DISTINCT "SiteTree"."ParentID", "SiteTree"."ID" FROM "SiteTree" 
			INNER JOIN "WorkflowRequest" ON "WorkflowRequest"."PageID" = "SiteTree"."ID"
			WHERE "WorkflowRequest"."Status" =  \'AwaitingApproval\'');
    }
}

/**
 * Fitler the SiteTree by pages awaiting publishing
 *
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class CMSWorkflowThreeStepFilters_PagesAwaitingPublishing extends CMSSiteTreeFilter
{
    public static function title()
    {
        return _t('CMSWorkflowThreeStepFilters_PagesAwaitingPublishing.TITLE', "Pages awaiting publishing");
    }
    
    public function pagesIncluded()
    {
        return DB::query('SELECT DISTINCT "SiteTree"."ParentID", "SiteTree"."ID" FROM "SiteTree" 
			INNER JOIN "WorkflowRequest" ON "WorkflowRequest"."PageID" = "SiteTree"."ID"
			WHERE "WorkflowRequest"."Status" =  \'Approved\'');
    }
}
