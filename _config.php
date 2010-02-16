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


SSReport::register('ReportAdmin', 'PagesDueForReviewReport');
SSReport::register('ReportAdmin', 'PagesScheduledForDeletionReport');
SSReport::register('ReportAdmin', 'PagesScheduledForPublishingReport');

?>