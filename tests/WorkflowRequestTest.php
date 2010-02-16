<?php
/**
 * @package cmsworkflow
 * @subpackage tests
 */
class WorkflowRequestTest extends FunctionalTest {
	
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
		
		$onLive = Versioned::get_by_stage('Page', 'Live', "SiteTree_Live.ID = ".$page->ID);
		$this->assertNull($onLive, 'Page has expired from live');
		
		SS_Datetime::clear_mock_now();
	}
	
	function testNotificationEmails() {
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
		
		$wf->approve('Looks good');

		$this->assertEmailSent(
			$customauthor->Email, // to
			$custompublisher->Email // from
		);
		
		$this->session()->inst_set('loggedInAs', null);
	}
	
	function testEachPageCanHaveOnlyOpenOpenRequest() {
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');
		
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		
		$request1 = $page->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');
		$request2 = $page->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$this->assertNotNull($request1);
		$this->assertEquals(
			$request1->ID,
			$request2->ID,
			'Each page can only have one open (not approved or declined) request'
		);
		
		$this->session()->inst_set('loggedInAs', null);
	}
	
	/**
	 * Test that openWorklow() and openOrNewWorkflow() function.
	 */
	function testOpenWorkflowRequest() {
		// Check a page that has an existing publication workflow
		$page = $this->objFromFixture('SiteTree', 'openpublishworkflowpage');
		$existingWorkflow = $this->objFromFixture('WorkflowPublicationRequest', 'openpublishworkflow');
	
		$this->assertEquals($existingWorkflow->ID, $page->openWorkflowRequest('WorkflowPublicationRequest')->ID);
		$this->assertEquals($existingWorkflow->ID, $page->openOrNewWorkflowRequest('WorkflowPublicationRequest')->ID);
		$this->assertNull($page->openWorkflowRequest('WorkflowDeletionRequest'));
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