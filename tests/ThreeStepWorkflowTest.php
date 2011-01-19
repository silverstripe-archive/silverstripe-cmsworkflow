<?php
/**
 * @package cmsworkflow
 * @subpackage tests
 */
class ThreeStepWorkflowTest extends FunctionalTest {
	
	static $fixture_file = 'cmsworkflow/tests/SiteTreeCMSWorkflowTest.yml';
	static $origSettings = array();

	protected $illegalExtensions = array(
		'SiteTree' => array('SiteTreeCMSTwoStepWorkflow'),
		'SiteConfig' => array('SiteConfigTwoStepWorkflow'),
		'WorkflowRequest' => array('WorkflowTwoStepRequest'),
	);

	protected $requiredExtensions = array(
		'SiteTree' => array('SiteTreeCMSThreeStepWorkflow'),
		'WorkflowRequest' => array('WorkflowThreeStepRequest'),
		'LeftAndMain' => array('LeftAndMainCMSThreeStepWorkflow'),
		'SiteConfig' => array('SiteConfigThreeStepWorkflow'),
	);
	
	static $extensionsToReapply = array();
	static $extensionsToRemoveAfter = array();
	
	function testWorkflowActions() {
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$customapprover = $this->objFromFixture('Member', 'customapprover');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		$this->logInAs($customauthor);
		
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');
		$request = $page->openOrNewWorkflowRequest('WorkflowPublicationRequest');
	
		WorkflowRequest::$allow_deny = true;
	
		// awaiting approval
			// author
			$this->logInAs($customauthor);
			$actions = array_flip($request->WorkflowActions());
			$this->assertContains('cms_cancel', $actions);
			$this->assertContains('cms_comment', $actions);
			$this->assertNotContains('cms_approve', $actions);
			$this->assertNotContains('cms_deny', $actions);
			// approver
			$this->logInAs($customapprover);
			$actions = array_flip($request->WorkflowActions());
			$this->assertContains('cms_cancel', $actions);
			$this->assertContains('cms_comment', $actions);
			$this->assertContains('cms_requestedit', $actions);
			$this->assertContains('cms_approve', $actions);
			$this->assertContains('cms_deny', $actions);
			// publisher
			$this->logInAs($custompublisher);
			$actions = array_flip($request->WorkflowActions());
			$this->assertContains('cms_cancel', $actions);
			$this->assertContains('cms_comment', $actions);
			$this->assertContains('cms_requestedit', $actions);
			$this->assertContains('cms_approve', $actions);
			$this->assertContains('cms_deny', $actions);
			$request->approve("app");
		// approved
			// author
			$this->logInAs($customauthor);
			$actions = array_flip($request->WorkflowActions());
			$this->assertContains('cms_cancel', $actions);
			$this->assertContains('cms_comment', $actions);
			$this->assertNotContains('cms_approve', $actions);
			$this->assertNotContains('cms_publish', $actions);
			$this->assertNotContains('cms_deny', $actions);
			// approver
			$this->logInAs($customapprover);
			$actions = array_flip($request->WorkflowActions());
			$this->assertContains('cms_cancel', $actions);
			$this->assertContains('cms_comment', $actions);
			$this->assertNotContains('cms_requestedit', $actions);
			$this->assertNotContains('cms_approve', $actions);
			$this->assertNotContains('cms_publish', $actions);
			$this->assertNotContains('cms_deny', $actions);
			// publisher
			$this->logInAs($custompublisher);
			$actions = array_flip($request->WorkflowActions());
			$this->assertContains('cms_cancel', $actions);
			$this->assertContains('cms_comment', $actions);
			$this->assertContains('cms_requestedit', $actions);
			$this->assertNotContains('cms_approve', $actions);
			$this->assertContains('cms_publish', $actions);
			$this->assertContains('cms_deny', $actions);
			$request->requestedit("reqed");
		// awaiting edit
			$actions = array_flip($request->WorkflowActions());
			$this->assertContains('cms_cancel', $actions);
			$this->assertContains('cms_comment', $actions);
			$this->assertContains('cms_requestpublication', $actions);
			$request->cancel("cancel");
	}
		
	function testWorkflowPublicationApprovalTransition() {
		WorkflowRequest::$enable_all_alerts = true;
		
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');
	
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$customapprover = $this->objFromFixture('Member', 'customapprover');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
	
		// nothing -> awaiting approval 
		$customauthor->logIn();
		$request = $page->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$request->request("ARGGGGG!");
		$this->assertNotNull($request);
		$this->assertEquals($request->AuthorID, $customauthor->ID, "Logged-in member is set as the author of the request");
		$this->assertEquals($request->Status, 'AwaitingApproval', "Request is set to AwaitingApproval after requestPublication() is called");
		
		// awaiting approval -> approved
		$customapprover->logIn();
		$request->approve('Looks good');
		$this->assertEquals($request->Status, 'Approved', "Request is set to Approved after page is approved");
		$this->assertEquals($request->ApproverID, $customapprover->ID, "Currently logged-in user is set as the Approver for this request");
		
		// place comment
		$customauthor->logIn();
		$request->comment("YARRRRRR!");
		
		// approved -> completed
		$custompublisher->logIn();
		$request->publish('Avast, ye scoundrels!', $custompublisher, false);
		$this->assertEquals($request->Status, 'Completed', "Request is set to Completed after page is published");
		$this->assertEquals($request->PublisherID, $custompublisher->ID, "Currently logged-in user is set as the Publisher for this request");
		
		// Test save and publish
		$this->objFromFixture('Member', 'admin')->logIn();
		$this->assertTrue(is_string($page->openOrNewWorkflowRequest('WorkflowPublicationRequest')->saveAndPublish("S&P")));
		
		// Test the get_by_* functions. These are cursory tests, covering functionality
		// but none of the multitude of edge cases
		$this->assertContains($page->ID, WorkflowThreeStepRequest::get_by_author('WorkflowPublicationRequest', $customauthor)->column('ID'));
		$this->assertContains($page->ID, WorkflowThreeStepRequest::get_by_approver('WorkflowPublicationRequest', $customapprover)->column('ID'));
		$this->assertContains($page->ID, WorkflowThreeStepRequest::get_by_publisher('WorkflowPublicationRequest', $custompublisher)->column('ID'));
	}
	
	function testManipulatingGroupsDuringAWorkflow() {
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');
	
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		$customauthorgroup = $this->objFromFixture('Group', 'customauthorsgroup');
	
		// awaiting approval 
		$customauthor->logIn();
		$request = $page->openOrNewWorkflowRequest('WorkflowPublicationRequest');
	
		// Asset publisher can approve but author cannot
		SiteTree::reset();
		$this->assertFalse($page->canApprove($customauthor));
		$this->assertTrue($page->canApprove($custompublisher));
		
		// Add the author group, assert they can now approve
		$page->CanApproveType = 'OnlyTheseUsers';
		$page->write();
		$page->ApproverGroups()->add($customauthorgroup);
		$this->assertTrue($page->canApprove($customauthor));
		
		$custompublisher->logIn();
	}
	
	function testEmbargoExpiry() {
		// Get fixtures
		$page = $this->objFromFixture('SiteTree', 'embargoexpirypage');
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
	
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		$request = $page->openWorkflowRequest('WorkflowPublicationRequest');
		$this->assertNotNull($request);
		
		$this->assertEquals(
			$request->AuthorID,
			$customauthor->ID,
			"Logged-in member is set as the author of the request"
		);
		
		// Ensure we can actually get the fields
		$this->assertNotNull($request->EmbargoField());
		$this->assertNotNull($request->ExpiryField());
		
		SS_Datetime::set_mock_now('2009-05-25 15:00:00');
		
		// Set embargo date to 01/06/2009 3:00pm, expriry to 7 days later
		$this->assertTrue($page->setEmbargo('01/06/2009', '3:00pm'), 'Setting embargo date');
		$this->assertTrue($page->setExpiry('07/06/2009', '3:00pm'), 'Settin expiry date');
		
		$request = $page->openWorkflowRequest('WorkflowPublicationRequest');
		
		// Login as publisher and approve page
		$custompublisher->logIn();
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);
		$this->assertEquals(true, $request->approve('Looks good. Will go out a bit later'),
			'Publisher ('.Member::currentUser()->Email.') can approve page');
	
		$request = $page->openWorkflowRequest('WorkflowPublicationRequest');
	
		$this->assertEquals(
			$request->Status,
			'Scheduled',
			"Request is set to Scheduled after approving a request with embargo and/or expriy dates set"
		);
		
		$sp = new ScheduledPublishing();
		$sp->suppressOutput();
		$sp->run(new SS_HTTPRequest('GET', '/'));
		
		$this->assertEquals(
			$request->Status,
			'Scheduled',
			"Request is still set to Scheduled after approving a request with embargo and/or expriy dates set, and running the publisher cron"
		);
		
		SS_Datetime::set_mock_now('2009-06-03 15:00:00');
		
		$sp->run(new SS_HTTPRequest('GET', '/'));
		
		$request = DataObject::get_by_id('WorkflowPublicationRequest', $request->ID);
		
		$this->assertEquals(
			$request->Status,
			'Completed',
			"Request is Completed after embargo date set"
		);
		
		SS_Datetime::set_mock_now('2009-06-15 15:00:00');
		$sp->run(new SS_HTTPRequest('GET', '/'));
		
		$onLive = Versioned::get_by_stage('Page', 'Live', "\"SiteTree_Live\".\"ID\" = ".$page->ID);
		$this->assertNull($onLive, 'Page has expired from live');
		
		SS_Datetime::clear_mock_now();
	}
	
	function testEmbargoExpiryWithVirtualPages() {
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$custompublisher->login();
		$sourcePage = new Page();
		$sourcePage->Content = '<p>Pre-embargo</p>';
		$sourcePage->write();
		$sourcePage->doPublish();
		
		$sourcePage->Content = '<p>Post-embargo</p>';
		$sourcePage->write();
		$request = $sourcePage->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$sourcePage->setEmbargo('01/06/2050', '3:00pm');
		$sourcePage->write();
		$request->approve('all good');
		
		$virtualPage = new VirtualPage();
		$virtualPage->CopyContentFromID = $sourcePage->ID;
		$virtualPage->write();
		$virtualPage->doPublish();
		
		$liveVirtualPage = Versioned::get_one_by_stage('VirtualPage', 'Live', '"SiteTree"."ID" = ' . $virtualPage->ID);
		$this->assertEquals($liveVirtualPage->Content, '<p>Pre-embargo</p>');
	}
	
	function testCmsFields() {
		$page = new Page();
		
		// Test fields as admin, make sure they are editable
		// $this->logInAs($this->objFromFixture('Member', 'admin'));
		// $form = $page->getCMSFields();
		// $this->assertTrue($form->fieldByName('Root.Access.CanApproveType') instanceof OptionsetField);
		// $this->assertTrue($form->fieldByName('Root.Access.ApproverGroups') instanceof TreeMultiselectField);
		// $this->assertTrue($form->fieldByName('Root.Access.CanPublishType') instanceof OptionsetField);
		// $this->assertTrue($form->fieldByName('Root.Access.PublisherGroups') instanceof TreeMultiselectField);

		// Test fields as admin, make sure they are editable
		$this->logInAs($this->objFromFixture('Member', 'randomuser'));
		$form = $page->getCMSFields();
		$this->assertFalse($form->fieldByName('Root.Access.CanApproveType') instanceof OptionsetField);
		$this->assertFalse($form->fieldByName('Root.Access.ApproverGroups') instanceof TreeMultiselectField);
		$this->assertFalse($form->fieldByName('Root.Access.CanPublishType') instanceof OptionsetField);
		$this->assertFalse($form->fieldByName('Root.Access.PublisherGroups') instanceof TreeMultiselectField);
	}
	
	function testBatchActionsAndFilters() {
		// Get fixtures
		$page1 = $this->objFromFixture('SiteTree', 'batchTest1');
		$page2 = $this->objFromFixture('SiteTree', 'batchTest2');
		$page3 = $this->objFromFixture('SiteTree', 'batchTest3');
		$page4 = $this->objFromFixture('SiteTree', 'batchTest4');
		$page5 = $this->objFromFixture('SiteTree', 'batchTest5');
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
	
		// Modify content
		$page1->Title = rand();$page1->write();
		$page2->Title = rand();$page2->write();
		$page3->Title = rand();$page3->write();
		$page4->Title = rand();$page4->write();
		$page5->Title = rand();$page5->write();
	
		// Create WF requests for each of em
		$customauthor->logIn();
		$wf1 = $page1->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$wf2 = $page2->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$wf3 = $page3->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$wf4 = $page4->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$wf5 = $page5->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		
		// // Create dataset
		$doSet = new DataObjectSet();
		$doSet->push($page1);
		$doSet->push($page2);
		$doSet->push($page3);
		$doSet->push($page4);
		
		// Test awaiting approval filters
		$filter = new CMSWorkflowThreeStepFilters_PagesAwaitingApproval();
		$this->assertTrue(is_string(CMSWorkflowThreeStepFilters_PagesAwaitingApproval::title()));
		$this->assertTrue($filter->isPageIncluded($page1));
		$this->assertTrue($filter->isPageIncluded($page2));
		$this->assertTrue($filter->isPageIncluded($page3));
		$this->assertTrue($filter->isPageIncluded($page4));
		
		// Batch approve
		$custompublisher->logIn();
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);
		
		$_REQUEST['ajax'] = 1;
		
		// Simulate response and request for batch action
		$controller = Controller::curr(); 
		$req = new NullHTTPRequest();
		$controller->handleRequest(new SS_HTTPRequest('GET', 'admin')); 
		
		$pageIds = $doSet->column('ID');
		
		$action = new BatchApprovePages();
		$this->assertTrue(is_string($action->getActionTitle()));
		$this->assertTrue(is_string($action->getDoingText()));
		$this->assertEquals($pageIds, $action->applicablePages($pageIds),
			'applicableIds only returns pages with open requests');
		$action->run($doSet);
		
		$page1 = DataObject::get_by_id('SiteTree', $pageIds[0]);
		$page2 = DataObject::get_by_id('SiteTree', $pageIds[1]);
		$page3 = DataObject::get_by_id('SiteTree', $pageIds[2]);
		$page4 = DataObject::get_by_id('SiteTree', $pageIds[3]);
		$doSet = new DataObjectSet();
		$doSet->push($page1);
		$doSet->push($page2);
		$doSet->push($page3);
		$doSet->push($page4);
		
		$this->assertEquals($page1->openWorkflowRequest()->Status, 'Approved', 'Workflow status is approved after batch action');
		$this->assertEquals($page2->openWorkflowRequest()->Status, 'Approved', 'Workflow status is approved after batch action');
		$this->assertEquals($page3->openWorkflowRequest()->Status, 'Approved', 'Workflow status is approved after batch action');
		$this->assertEquals($page4->openWorkflowRequest()->Status, 'Approved', 'Workflow status is approved after batch action');
	
		// Test awaiting publication filters
		$filter = new CMSWorkflowThreeStepFilters_PagesAwaitingPublishing();
		$this->assertTrue(is_string(CMSWorkflowThreeStepFilters_PagesAwaitingPublishing::title()));
		$this->assertTrue($filter->isPageIncluded($page1));
		$this->assertTrue($filter->isPageIncluded($page2));
		$this->assertTrue($filter->isPageIncluded($page3));
		$this->assertTrue($filter->isPageIncluded($page4));
		
		// Batch publish
		$action = new BatchPublishPages();
		$this->assertTrue(is_string($action->getActionTitle()));
		$this->assertTrue(is_string($action->getDoingText()));
		$this->assertEquals($pageIds, $action->applicablePages($doSet->column('ID')),
			'applicableIds only returns pages with open requests');
		$action->run($doSet);
		
		$page1 = DataObject::get_by_id('SiteTree', $pageIds[0]);
		$page2 = DataObject::get_by_id('SiteTree', $pageIds[1]);
		$page3 = DataObject::get_by_id('SiteTree', $pageIds[2]);
		$page4 = DataObject::get_by_id('SiteTree', $pageIds[3]);
		
		$this->assertNull($page1->openWorkflowRequest(), 'No open workflow after publishing live');
		$this->assertNull($page2->openWorkflowRequest(), 'No open workflow after publishing live');
		$this->assertNull($page3->openWorkflowRequest(), 'No open workflow after publishing live');
		$this->assertNull($page4->openWorkflowRequest(), 'No open workflow after publishing live');
	}
	
	function testSiteConfigFields() {
		// Ensure admins can see the permission fields and edit them
		$this->logInAs($this->objFromFixture('Member', 'admin'));
		
		$fields = singleton('SiteConfig')->getCMSFields();
		$this->assertNotNull($fields->fieldByName('Root.Access.CanPublishType'));
		$this->assertNotNull($fields->fieldByName('Root.Access.PublisherGroups'));
		$this->assertNotNull($fields->fieldByName('Root.Access.ApproverGroups'));
		$this->assertFalse($fields->fieldByName('Root.Access.CanPublishType') instanceof ReadonlyField);
		$this->assertFalse($fields->fieldByName('Root.Access.PublisherGroups') instanceof ReadonlyField);
		$this->assertFalse($fields->fieldByName('Root.Access.ApproverGroups') instanceof ReadonlyField);
		
		// Make sure none admins can see them, but not edit
		$this->logInAs($this->objFromFixture('Member', 'customauthor'));
		$fields = singleton('SiteConfig')->getCMSFields();
		$this->assertTrue($fields->fieldByName('Root.Access.CanPublishType') instanceof LookupField);
		$this->assertTrue($fields->fieldByName('Root.Access.CanApproveType') instanceof LookupField);
		$this->assertTrue($fields->fieldByName('Root.Access.PublisherGroups') instanceof TreeMultiselectField_Readonly);
		$this->assertTrue($fields->fieldByName('Root.Access.ApproverGroups') instanceof TreeMultiselectField_Readonly);
	}
	
	function testSiteConfigMemberRetrievalFunctions() {
		$sc = SiteConfig::current_site_config();
		
		$sc->CanPublishType = null;
		$sc->CanApproveType = null;
		$sc->PublisherGroups()->removeAll();
		$sc->ApproverGroups()->removeAll();
		
		$pEmails = $sc->PublisherMembers()->column('Email');
		$this->assertTrue(in_array(strtolower($pEmails[0]),array('admin@example.org','admin@test.com')),'With CanPublishType set to null, should return admins');
		$aEmails = $sc->ApproverMembers()->column('Email');
		$this->assertTrue(in_array(strtolower($aEmails[0]),array('admin@example.org','admin@test.com')), 'With CanApproveType set to null, should return admins');
		
		
		$sc->CanPublishType = 'OnlyTheseUsers';
		$sc->CanApproveType = 'OnlyTheseUsers';
		$sc->PublisherGroups()->removeAll();
		$sc->ApproverGroups()->removeAll();
		
		$pEmails = $sc->PublisherMembers()->column('Email');
		$this->assertTrue(in_array(strtolower($pEmails[0]),array('admin@example.org','admin@test.com')), 'With CanPublishType set to OnlyTheseUsers, but no groups set up, should return admins');
		
		$aEmails = $sc->ApproverMembers()->column('Email');
		$this->assertTrue(in_array(strtolower($aEmails[0]),array('admin@example.org','admin@test.com')), 'With CanApproveType set to OnlyTheseUsers, but no groups set up, should return admins');
		
		// Should now return two authors
		$sc->PublisherGroups()->add($this->objFromFixture('Group', 'customauthorsgroup'));
		$compare1=$sc->PublisherMembers()->column('Email');
		$compare2=array('customauthor@test.com','customauthor2@test.com');
		sort($compare1);
		sort($compare2);
		$this->assertEquals($compare1, $compare2);
		
		$sc->ApproverGroups()->add($this->objFromFixture('Group', 'customauthorsgroup'));
		$compare1=$sc->ApproverMembers()->column('Email');
		$compare2=array('customauthor@test.com','customauthor2@test.com');
		sort($compare1);
		sort($compare2);
		$this->assertEquals($compare1, $compare2);
	
		$sc->CanPublishType = 'LoggedInUsers';
		$this->assertEquals(4, $sc->PublisherMembers()->Count(), 'PublisherMembers returns the 4 users that have CMS access');
		$sc->CanApproveType = 'LoggedInUsers';
		$this->assertEquals(4, $sc->ApproverMembers()->Count(), 'ApproverMembers returns the 4 users that have CMS access');
	}
	
	function testPageMemberRetrievalFunctions() {
		$sc = SiteConfig::current_site_config();
		$sc->CanPublishType = 'OnlyTheseUsers';
		$sc->PublisherGroups()->removeAll();
		$sc->PublisherGroups()->add($this->objFromFixture('Group', 'customauthorsgroup'));
		$sc->write();
		$sc->CanApproveType = 'OnlyTheseUsers';
		$sc->ApproverGroups()->removeAll();
		$sc->ApproverGroups()->add($this->objFromFixture('Group', 'customauthorsgroup'));
		$sc->write();
		
		$page = new Page();
		
		// Test inherit
		$page->CanPublishType = 'Inherit';
		$compare1=$page->PublisherMembers()->column('Email');
		$compare2=array('customauthor@test.com','customauthor2@test.com');
		sort($compare1);
		sort($compare2);
		$this->assertEquals($compare1, $compare2);
		$page->CanApproveType = 'Inherit';
		$compare1=$page->ApproverMembers()->column('Email');
		$compare2=array('customauthor@test.com', 'customauthor2@test.com');
		sort($compare1);
		sort($compare2);
		$this->assertEquals($compare1, $compare2);
		
		// Test specific groups
		$page->CanPublishType = 'OnlyTheseUsers';
		$pEmails = $page->PublisherMembers()->column('Email');
		$this->assertTrue(in_array(strtolower($pEmails[0]),array('admin@example.org','admin@test.com')), 'With CanPublishType set to OnlyTheseUsers, but no groups set up, should return admins');
		
		$page->PublisherGroups()->add($this->objFromFixture('Group', 'custompublishergroup'));
		$this->assertEquals($page->PublisherMembers()->column('Email'), array(
			'publisher@test.com'
		));
		
		$page->CanApproveType = 'OnlyTheseUsers';
		$aEmails = $page->ApproverMembers()->column('Email');
		$this->assertTrue(in_array(strtolower($aEmails[0]),array('admin@example.org','admin@test.com')),'With CanApproveType set to OnlyTheseUsers, but no groups set up, should return admins');
		
		$page->ApproverGroups()->add($this->objFromFixture('Group', 'custompublishergroup'));
		$this->assertEquals($page->ApproverMembers()->column('Email'), array(
			'publisher@test.com'
		));
		
		
		$page->CanPublishType = 'Inherit';
		$page->CanApproveType = 'Inherit';
		$page->write();
		
		// Test passthru methods
		$this->assertTrue($page->canPublish($this->objFromFixture('Member', 'customauthor')));
		$this->assertTrue($page->canApprove($this->objFromFixture('Member', 'customauthor')));
		$this->assertTrue($page->canRequestEdit($this->objFromFixture('Member', 'customauthor')));

		// Test 'all' users
		$page->CanPublishType = 'LoggedInUsers';
		$page->CanApproveType = 'LoggedInUsers';
		$this->assertEquals(4, $page->PublisherMembers()->Count(), 'PublisherMembers returns the 4 users that have CMS access');
		$this->assertEquals(4, $page->ApproverMembers()->Count(), 'ApproverMembers returns the 4 users that have CMS access');
	}
}
?>
