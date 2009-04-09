<?php
/**
 * @package cmsworkflow
 * @subpackage tests
 */
class WorkflowRequestTest extends FunctionalTest {
	
	static $fixture_file = 'cmsworkflow/tests/SiteTreeCMSWorkflowTest.yml';
	
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
		
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);

		$request1->approve('Looks good');

		$this->assertEquals(
			$request1->Status,
			'Approved',
			"Request is set to Approved after page is published"
		);
		$this->assertEquals(
			$request1->PublisherID,
			$custompublisher->ID,
			"Currently logged-in user is set as the Publisher for this request"
		);
		
		$this->session()->inst_set('loggedInAs',null);
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
	
	// Commenting this out, is this really a necessary limitation?
	/*
	function testSecondRequestOpeningDeniedIfDifferentAuthor() {
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');

		$customauthorsgroup = $this->objFromFixture('Group', 'customauthorsgroup');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		$customauthor->Groups()->add($customauthorsgroup);
		$customauthor2 = $this->objFromFixture('Member', 'customauthor2');
		$customauthor2->Groups()->add($customauthorsgroup);
		
		$this->session()->inst_set('loggedInAs', $customauthor->ID);

		// first request
		$request1 = $page->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		// second request by original author
		$request2 = $page->openOrNewWorkflowRequest('WorkflowPublicationRequest');

		$this->assertEquals(
			$request1->ID,
			$request2->ID,
			'Each page can only have one open (not approved or declined) request'
		);
		
		// second request by other author
		$this->session()->inst_set('loggedInAs', $customauthor2->ID);
		$request3 = $page->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$this->assertFalse(
			$request3,
			'If open request exists, a member who is not the author of the original request cant create a new request'
		);
		
		$this->session()->inst_set('loggedInAs', null);
	}
	*/
	
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
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');
		$page->doPublish();

		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		
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
			'Approved',
		), $changes->column('Status'));
	}
}
?>