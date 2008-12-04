<?php
/**
 * @package cmsworkflow
 * @subpackage tests
 */
class WorkflowRequestTest extends FunctionalTest {
	
	static $fixture_file = 'cmsworkflow/tests/SiteTreeCMSWorkflowTest.yml';
	
	function testWorkflowPublicationApprovalTransition() {
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');

		$custompublishersgroup = $this->objFromFixture('Group', 'custompublishergroup');
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$custompublisher->Groups()->add($custompublishersgroup);

		$customauthorsgroup = $this->objFromFixture('Group', 'customauthorsgroup');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		$customauthor->Groups()->add($customauthorsgroup);
		
		// awaiting approval 
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		$page->requestPublication($customauthor, $page->PublisherMembers());
		$request1 = $page->OpenWorkflowRequest();
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
		$page->doPublish();
		$page->flushCache();
		$request1 = DataObject::get_by_id('WorkflowRequest', $request1->ID);
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

		$custompublishersgroup = $this->objFromFixture('Group', 'custompublishergroup');
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$custompublisher->Groups()->add($custompublishersgroup);

		$customauthorsgroup = $this->objFromFixture('Group', 'customauthorsgroup');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		$customauthor->Groups()->add($customauthorsgroup);
		
		// awaiting approval emails
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		$page->requestPublication($customauthor, $page->PublisherMembers());
		$this->assertEmailSent(
			$custompublisher->Email, // to
			$customauthor->Email // from
		);
		
		// published emails
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);
		// doesn't work because onAfterWrite() is not called
		//$page->publish('Stage','Live');
		$page->doPublish();
		$this->assertEmailSent(
			$customauthor->Email, // to
			$custompublisher->Email // from
		);
		
		$this->session()->inst_set('loggedInAs', null);
	}
	
	function testEachPageCanHaveOnlyOpenOpenRequest() {
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');
		
		$custompublishersgroup = $this->objFromFixture('Group', 'custompublishergroup');
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$custompublisher->Groups()->add($custompublishersgroup);

		$customauthorsgroup = $this->objFromFixture('Group', 'customauthorsgroup');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		$customauthor->Groups()->add($customauthorsgroup);
		
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		$page->requestPublication($customauthor, $page->PublisherMembers());
		$request1 = $page->OpenWorkflowRequest();
		
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');
		$page->requestPublication($customauthor, $page->PublisherMembers());
		$request2 = $page->OpenWorkflowRequest();
		$this->assertEquals(
			$request1->ID,
			$request2->ID,
			'Each page can only have one open (not approved or declined) request'
		);
		
		$this->session()->inst_set('loggedInAs', null);
	}
}
?>