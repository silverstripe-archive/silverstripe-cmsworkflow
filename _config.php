<?php
Object::add_extension('SiteTree', 'SiteTreeCMSWorkflow');
Object::add_extension('LeftAndMain', 'LeftAndMainCMSWorkflow');
Object::add_extension('Member', 'WorkflowMemberRole');

SiteTreeCMSWorkflow::register_request('WorkflowPublicationRequest');
SiteTreeCMSWorkflow::register_request('WorkflowDeletionRequest');

Director::addRules(200, array(
	'admin/changes.rss' => '->admin/cms/changes.rss',
	'admin/cms/changes.rss' => array('Controller' => 'CMSChangeTracker', 'Data' => 'all'),
	// These still need to be implemented :-P
	//'admin/cms/page/$PageID/changes.rss' => array('Controller' => 'CMSChangeTracker', 'Data' => 'page'),
	//'admin/cms/subtree-of/$PageID/changes.rss' => array('Controller' => 'CMSChangeTracker', 'Data' => 'subtree'),
	//'admin/cms/children-of/$PageID/changes.rss' => array('Controller' => 'CMSChangeTracker', 'Data' => 'children'),
));

?>