<?php
Object::add_extension('SiteTree', 'SiteTreeCMSWorkflow');
Object::add_extension('LeftAndMain', 'LeftAndMainCMSWorkflow');
Object::add_extension('Member', 'WorkflowMemberRole');

SiteTreeCMSWorkflow::register_request('WorkflowPublicationRequest');
SiteTreeCMSWorkflow::register_request('WorkflowDeletionRequest');

// Defaults to a "two step" workflow.
WorkflowTwoStepRequest::apply_alerts();
Object::add_extension('WorkflowRequest', 'WorkflowTwoStepRequest');
Object::add_extension('SiteTree', 'SiteTreeCMSTwoStepWorkflow');
Object::add_extension('SiteConfig', 'SiteConfigTwoStepWorkflow');

// To enable ThreeStep, remove the two Object:: lines above, and uncomment these.
// WorkflowThreeStepRequest::apply_alerts();
// Object::add_extension('WorkflowRequest', 'WorkflowThreeStepRequest');
// Object::add_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
// Object::add_extension('SiteConfig', 'SiteConfigThreeStepWorkflow');
// Object::add_extension('LeftAndMain', 'LeftAndMainCMSThreeStepWorkflow');
// unset(CMSBatchActionHandler::$batch_actions['publish']);

Director::addRules(200, array(
	'admin/changes.rss' => '->admin/cms/changes.rss',
	'admin/cms/changes.rss' => array('Controller' => 'CMSChangeTracker', 'Data' => 'all'),
));

unset(CMSBatchActionHandler::$batch_actions['publish']);


// Register main reports
if(class_exists('Subsite')) {
	SSReport::register('ReportAdmin', 'SubsiteReportWrapper("UnapprovedPublications3StepReport")',20);
	SSReport::register('ReportAdmin', 'SubsiteReportWrapper("ApprovedPublications3StepReport")',20);
	SSReport::register('ReportAdmin', 'SubsiteReportWrapper("UnapprovedDeletions3StepReport")',20);
	SSReport::register('ReportAdmin', 'SubsiteReportWrapper("ApprovedDeletions3StepReport")',20);
	SSReport::register('ReportAdmin', 'SubsiteReportWrapper("PagesScheduledForPublishingReport")',20);
	SSReport::register('ReportAdmin', 'SubsiteReportWrapper("PagesScheduledForDeletionReport")',20);
	SSReport::register('ReportAdmin', 'SubsiteReportWrapper("PagesDueForReviewReport")',20);
	SSReport::register('ReportAdmin', 'SubsiteReportWrapper("PublishingReport")',20);
	
	
} else {
	SSReport::register('ReportAdmin', 'UnapprovedPublications3StepReport',20);
	SSReport::register('ReportAdmin', 'ApprovedPublications3StepReport',20);
	SSReport::register('ReportAdmin', 'UnapprovedDeletions3StepReport',20);
	SSReport::register('ReportAdmin', 'ApprovedDeletions3StepReport',20);
	SSReport::register('ReportAdmin', 'PagesScheduledForPublishingReport',20);
	SSReport::register('ReportAdmin', 'PagesScheduledForDeletionReport',20);
	SSReport::register('ReportAdmin', 'PagesDueForReviewReport',20);
	SSReport::register('ReportAdmin', 'PublishingReport',20);
}


// Register site reports

// 2 Step
SSReport::register('SideReport', 'MyTwoStepDeletionRequestsSideReport', 20);
SSReport::register('SideReport', 'MyTwoStepPublicationRequestsSideReport', 20);
SSReport::register('SideReport', 'MyTwoStepWorkflowRequestsSideReport', 20);

// 3 Step
SSReport::register('SideReport', 'SideReportWrapper("ThreeStepMyPublicationRequestsSideReport")', 20);
SSReport::register('SideReport', 'SideReportWrapper("ThreeStepMyDeletionRequestsSideReport")', 20);
SSReport::register('SideReport', 'SideReportWrapper("UnapprovedPublications3StepReport")', 20);
SSReport::register('SideReport', 'SideReportWrapper("ApprovedPublications3StepReport")', 20);
SSReport::register('SideReport', 'SideReportWrapper("UnapprovedDeletions3StepReport")', 20);
SSReport::register('SideReport', 'SideReportWrapper("ApprovedDeletions3StepReport")', 20);


?>