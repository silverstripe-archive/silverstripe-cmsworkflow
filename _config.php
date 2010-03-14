<?php
Object::add_extension('SiteTree', 'SiteTreeCMSWorkflow');
Object::add_extension('LeftAndMain', 'LeftAndMainCMSWorkflow');
Object::add_extension('Member', 'WorkflowMemberRole');

Object::add_extension('SiteTree', 'SiteTreeFutureState');


SiteTreeCMSWorkflow::register_request('WorkflowPublicationRequest');
SiteTreeCMSWorkflow::register_request('WorkflowDeletionRequest');

// Defaults to a "two step" workflow.
Object::add_extension('WorkflowRequest', 'WorkflowTwoStepRequest');
Object::add_extension('SiteTree', 'SiteTreeCMSTwoStepWorkflow');
Object::add_extension('SiteConfig', 'SiteConfigTwoStepWorkflow');

// To enable ThreeStep, remove the two Object:: lines above, and uncomment these.
// Object::add_extension('WorkflowRequest', 'WorkflowThreeStepRequest');
// Object::add_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
// Object::add_extension('SiteConfig', 'SiteConfigThreeStepWorkflow');
// Object::add_extension('LeftAndMain', 'LeftAndMainCMSThreeStepWorkflow');

Director::addRules(200, array(
	'admin/changes.rss' => '->admin/cms/changes.rss',
	'admin/cms/changes.rss' => array('Controller' => 'CMSChangeTracker', 'Data' => 'all'),
));


// Register main reports
if(class_exists('Subsite') && class_exists('SubsiteReportWrapper')) {
	SS_Report::register('ReportAdmin', 'SubsiteReportWrapper("UnapprovedPublications3StepReport")',20);
	SS_Report::register('ReportAdmin', 'SubsiteReportWrapper("ApprovedPublications3StepReport")',20);
	SS_Report::register('ReportAdmin', 'SubsiteReportWrapper("UnapprovedDeletions3StepReport")',20);
	SS_Report::register('ReportAdmin', 'SubsiteReportWrapper("ApprovedDeletions3StepReport")',20);
	SS_Report::register('ReportAdmin', 'SubsiteReportWrapper("PagesScheduledForPublishingReport")',20);
	SS_Report::register('ReportAdmin', 'SubsiteReportWrapper("PagesScheduledForDeletionReport")',20);
	SS_Report::register('ReportAdmin', 'SubsiteReportWrapper("RecentlyPublishedPagesReport")',20);
} else {
	SS_Report::register('ReportAdmin', 'UnapprovedPublications3StepReport',20);
	SS_Report::register('ReportAdmin', 'ApprovedPublications3StepReport',20);
	SS_Report::register('ReportAdmin', 'UnapprovedDeletions3StepReport',20);
	SS_Report::register('ReportAdmin', 'ApprovedDeletions3StepReport',20);
	SS_Report::register('ReportAdmin', 'PagesScheduledForPublishingReport',20);
	SS_Report::register('ReportAdmin', 'PagesScheduledForDeletionReport',20);
	SS_Report::register('ReportAdmin', 'RecentlyPublishedPagesReport',20);
}


// Register site reports

// 2 Step
SS_Report::register('SideReport', 'MyTwoStepDeletionRequests', 20);
SS_Report::register('SideReport', 'MyTwoStepPublicationRequests', 20);
SS_Report::register('SideReport', 'MyTwoStepWorkflowRequests', 20);

// 3 Step
SS_Report::register('SideReport', 'SideReportWrapper("ThreeStepMyPublicationRequestsSideReport")', 20);
SS_Report::register('SideReport', 'SideReportWrapper("ThreeStepMyDeletionRequestsSideReport")', 20);
SS_Report::register('SideReport', 'SideReportWrapper("UnapprovedPublications3StepReport")', 20);
SS_Report::register('SideReport', 'SideReportWrapper("ApprovedPublications3StepReport")', 20);
SS_Report::register('SideReport', 'SideReportWrapper("UnapprovedDeletions3StepReport")', 20);
SS_Report::register('SideReport', 'SideReportWrapper("ApprovedDeletions3StepReport")', 20);


?>