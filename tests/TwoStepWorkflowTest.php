<?php
/**
 * @package cmsworkflow
 * @subpackage tests
 */
class TwoStepWorkflowTest extends FunctionalTest {
	
	static $fixture_file = 'cmsworkflow/tests/SiteTreeCMSWorkflowTest.yml';

	protected $requiredExtensions = array(
		'SiteTree' => array('SiteTreeCMSTwoStepWorkflow'),
		'WorkflowRequest' => array('WorkflowTwoStepRequest'),
	);

	protected $illegalExtensions = array(
		'SiteTree' => array('SiteTreeCMSThreeStepWorkflow'),
		'WorkflowRequest' => array('WorkflowThreeStepRequest'),
		'LeftAndMain' => array('LeftAndMainCMSThreeStepWorkflow'),
		'SiteConfig' => array('SiteConfigThreeStepWorkflow'),
	);
	
	function testWorkflowPublicationApprovalTransition() {
		WorkflowRequest::$enable_all_alerts = true;
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');
	
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
	
		// awaiting approval 
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		$request1 = $page->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$this->assertNotNull($request1);
		$this->assertEquals(
			$request1->AuthorID,
			$customauthor->ID,
			"Logged-in member is set as the author of the request"
		);
		$this->assertEquals(
			$request1->Status,
			'AwaitingApproval',
			"Request is set to AwaitingApproval after requestPublication() is called"
		);

		$this->assertContains(
			'cms_cancel',
			array_flip($request1->WorkflowActions()),
			"Author can cancel own request"
		);
		
		$this->assertNotContains(
			'cms_deny',
			array_flip($request1->WorkflowActions()),
			"Author cant deny own request"
		);
		
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);
	
		$this->assertContains(
			'cms_cancel',
			array_flip($request1->WorkflowActions()),
			"Publisher can cancel requests"
		);
		
		$request1->requestedit('Please make some changes');
		$request1->comment('Here are the changes I would like you to do:');
		
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		
		$this->assertContains(
			'cms_requestpublication',
			array_flip($request1->WorkflowActions()),
			"Author can resubmit after request edit"
		);
		$request1->request('Resubmit after edits');
		
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);
		
		WorkflowRequest::$allow_deny = true;
	
		$this->assertContains(
			'cms_deny',
			array_flip($request1->WorkflowActions()),
			"Publisher can deny requests when WorkflowRequest::allow_deny is true"
		);
		
		WorkflowRequest::$allow_deny = false;
		
		$this->assertNotContains(
			'cms_deny',
			array_flip($request1->WorkflowActions()),
			"Publisher can't deny requests when WorkflowRequest::allow_deny is false"
		);
		
		WorkflowRequest::$allow_deny = true;
		
		$request1->approve('Looks good');
	
		$this->assertEquals(
			$request1->Status,
			'Completed',
			"Request is set to Completed after page is approved"
		);
		$this->assertEquals(
			$request1->PublisherID,
			$custompublisher->ID,
			"Currently logged-in user is set as the Publisher for this request"
		);
		
		$this->session()->inst_set('loggedInAs',null);
		
		// Make sure the requests can be picked up by the get by author and get by publisher functions
		$this->assertContains($request1->Page()->ID, WorkflowTwoStepRequest::get_by_publisher('WorkflowPublicationRequest', $custompublisher, array('Completed'))->column('ID'));
		$this->assertContains($request1->Page()->ID, WorkflowTwoStepRequest::get_by_author('WorkflowPublicationRequest', $customauthor, array('Completed'))->column('ID'));
		
		WorkflowRequest::$enable_all_alerts = false;
	}
	
	function testSiteConfigFields() {
		// Ensure admins can see the permission fields and edit them
		$this->logInAs($this->objFromFixture('Member', 'admin'));
		$this->assertTrue(is_array(singleton('SiteConfigTwoStepWorkflow')->extraStatics()));
		$fields = singleton('SiteConfig')->getCMSFields();
		$this->assertNotNull($fields->fieldByName('Root.Access.CanPublishType'));
		$this->assertNotNull($fields->fieldByName('Root.Access.PublisherGroups'));
		$this->assertFalse($fields->fieldByName('Root.Access.CanPublishType') instanceof ReadonlyField);
		$this->assertFalse($fields->fieldByName('Root.Access.PublisherGroups') instanceof ReadonlyField);
		
		// Make sure none admins can see them, but not edit
		$this->logInAs($this->objFromFixture('Member', 'customauthor'));
		$fields = singleton('SiteConfig')->getCMSFields();
		$this->assertTrue($fields->fieldByName('Root.Access.CanPublishType') instanceof LookupField);
		$this->assertTrue($fields->fieldByName('Root.Access.PublisherGroups') instanceof TreeMultiselectField_Readonly);
	}
	
	function testSiteConfigMemberRetrievalFunctions() {
		$sc = SiteConfig::current_site_config();
		
		$sc->CanPublishType = null;
		$sc->PublisherGroups()->removeAll();
		$this->assertEquals($sc->PublisherMembers()->column('Email'), array(
			'admin@test.com'
		), 'With CanPublishType set to null, should return admins');
		
		
		$sc->CanPublishType = 'OnlyTheseUsers';
		$sc->PublisherGroups()->removeAll();
		
		$this->assertEquals($sc->PublisherMembers()->column('Email'), array(
			'admin@test.com'
		), 'With CanPublishType set to OnlyTheseUsers, but no groups set up, should return admins');
		
		// Should now return two authors
		$sc->PublisherGroups()->add($this->objFromFixture('Group', 'customauthorsgroup'));
		$compare1=$sc->PublisherMembers()->column('Email');
		$compare2=array('customauthor@test.com','customauthor2@test.com');
		sort($compare1);
		sort($compare2);
		$this->assertEquals($compare1, $compare2);

		$sc->CanPublishType = 'LoggedInUsers';
		$this->assertEquals(4, $sc->PublisherMembers()->Count(), 'PublisherMembers returns the 4 users that have CMS access');
	}
	
	function testPageMemberRetrievalFunctions() {
		$sc = SiteConfig::current_site_config();
		$sc->CanPublishType = 'OnlyTheseUsers';
		$sc->PublisherGroups()->removeAll();
		$sc->PublisherGroups()->add($this->objFromFixture('Group', 'customauthorsgroup'));
		$sc->write();
		
		$page = new Page();
		
		// Test inherit
		$page->CanPublishType = 'Inherit';
		$compare1=$page->PublisherMembers()->column('Email');
		$compare2=array('customauthor@test.com','customauthor2@test.com');
		
		sort($compare1);
		sort($compare2);
		
		$this->assertEquals($compare1, $compare2);
		
		// Test specific groups
		$page->CanPublishType = 'OnlyTheseUsers';
		$this->assertEquals($page->PublisherMembers()->column('Email'), array(
			'admin@test.com'
		), 'With CanPublishType set to OnlyTheseUsers, but no groups set up, should return admins');
		
		$page->PublisherGroups()->add($this->objFromFixture('Group', 'custompublishergroup'));
		$this->assertEquals($page->PublisherMembers()->column('Email'), array(
			'publisher@test.com'
		));
		
		
		$page->CanPublishType = 'Inherit';
		$page->write();
		
		// Test passthru methods
		$this->assertTrue($page->canPublish($this->objFromFixture('Member', 'customauthor')));
		$this->assertTrue($page->canApprove($this->objFromFixture('Member', 'customauthor')));
		$this->assertTrue($page->canRequestEdit($this->objFromFixture('Member', 'customauthor')));

		// Test 'all' users
		$page->CanPublishType = 'LoggedInUsers';
		$this->assertEquals(4, $page->PublisherMembers()->Count(), 'PublisherMembers returns the 4 users that have CMS access');
	}

	function testManipulatingGroupsDuringAWorkflow() {
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');
	
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		$customauthorgroup = $this->objFromFixture('Group', 'customauthorsgroup');
	
		// awaiting approval 
		$customauthor->logIn();
		$request = $page->openOrNewWorkflowRequest('WorkflowPublicationRequest');
	
		// Asset publisher can publish but author cannot
		$this->assertFalse($page->canPublish($customauthor));
		$this->assertTrue($page->canPublish($custompublisher));
		
		// Add the author group, assert they can now publish
		$page->CanPublishType = 'OnlyTheseUsers';
		$page->write();
		$page->PublisherGroups()->add($customauthorgroup);
		$this->assertTrue($page->canPublish($customauthor));
		
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
	
	function testNotificationEmails() {
		WorkflowRequest::$enable_all_alerts = true;
		
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');
	
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		
		// awaiting approval emails
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		$wf = $page->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$this->assertNotNull($wf);
		$wf->request("Can you publish this please?");
		
		$this->assertEmailSent(
			$custompublisher->Email, // to
			$customauthor->Email // from
		);
		
		// published emails
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);
		// doesn't work because onAfterWrite() is not called
		//$page->publish('Stage','Live');
		
		// Save and publish is an alias for approve
		$wf->saveAndPublish('Looks good');
	
		$this->assertEmailSent(
			$customauthor->Email, // to
			$custompublisher->Email // from
		);
		
		$this->session()->inst_set('loggedInAs', null);
		
		WorkflowRequest::$enable_all_alerts = false;
	}
	
	/**
	 * Confirm that an array of comments is created on a workflow
	 */
	function testCommentThread() {
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		
		$custompublisher->logIn();
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');
		$page->doPublish();
		
		$customauthor->logIn();
		$wf = $page->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$wf->request("Can you please publish this?");
		
		$custompublisher->logIn();
		$wf->deny("No, you've got a spelling mistake.");
	
		$customauthor->logIn();
		$wf->request("Is it better now?");
		
		$custompublisher->logIn();
		$wf->approve("Yes, looks good now.");
		
		$this->session()->inst_set('loggedInAs', null);
		
		$changes = $wf->Changes();
		$this->assertEquals(array(
			"Can you please publish this?",
			"No, you've got a spelling mistake.",
			"Is it better now?",
			"Yes, looks good now.",
		), $changes->column('Comment'));
		$this->assertEquals(array(
			$customauthor->ID,
			$custompublisher->ID,
			$customauthor->ID,
			$custompublisher->ID,
		), $changes->column('AuthorID'));
		$this->assertEquals(array(
			'AwaitingApproval',
			'Denied',
			'AwaitingApproval',
			'Completed',
		), $changes->column('Status'));
	}
}
?>