<?php
Object::add_extension('SiteTree', 'SiteTreeCMSWorkflow');
Object::add_extension('LeftAndMain', 'LeftAndMainCMSWorkflow');
Object::add_extension('Member', 'WorkflowMemberRole');

SiteTreeCMSWorkflow::register_request('WorkflowPublicationRequest');
SiteTreeCMSWorkflow::register_request('WorkflowDeletionRequest');

// Defaults to a "two step" workflow.
// See README for instructions to enable "three step" workflow instead.
Object::add_extension('WorkflowRequest', 'WorkflowTwoStepRequest');
Object::add_extension('SiteTree', 'SiteTreeCMSTwoStepWorkflow');

Director::addRules(200, array(
	'admin/changes.rss' => '->admin/cms/changes.rss',
	'admin/cms/changes.rss' => array('Controller' => 'CMSChangeTracker', 'Data' => 'all'),
	// These still need to be implemented :-P
	//'admin/cms/page/$PageID/changes.rss' => array('Controller' => 'CMSChangeTracker', 'Data' => 'page'),
	//'admin/cms/subtree-of/$PageID/changes.rss' => array('Controller' => 'CMSChangeTracker', 'Data' => 'subtree'),
	//'admin/cms/children-of/$PageID/changes.rss' => array('Controller' => 'CMSChangeTracker', 'Data' => 'children'),
));

unset(CMSBatchActionHandler::$batch_actions['publish']);
unset(CMSBatchActionHandler::$batch_actions['delete']);
unset(CMSBatchActionHandler::$batch_actions['deletefromlive']);
unset(CMSBatchActionHandler::$batch_actions['unpublish']);

?>