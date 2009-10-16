<?php
/**
 * @package cmsworkflow
 * @subpackage tests
 */
class ThreeStepWorkflowTest extends FunctionalTest {
	
	static $fixture_file = 'cmsworkflow/tests/SiteTreeCMSWorkflowTest.yml';
	static $origSettings = array();
	
	// These extensions will be reapplied when the time comes
	// if they were applied.	
	static $extensionsToRemove = array(
		'SiteTree' => array(
			'SiteTreeCMSTwoStepWorkflow',
			'SiteTreeCMSThreeStepWorkflow'
		), 'WorkflowRequest' => array(
			'WorkflowTwoStepRequest',
			'WorkflowThreeStepRequest'
		), 'LeftAndMain' => array(
			'LeftAndMainCMSThreeStepWorkflow'
		)
	);
	
	static $extensionsToApply = array(
		'SiteTree' => array(
			'SiteTreeCMSThreeStepWorkflow'
		), 'WorkflowRequest' => array(
			'WorkflowThreeStepRequest'
		), 'LeftAndMain' => array(
			'LeftAndMainCMSThreeStepWorkflow'
		), 'SiteConfig' => array(
			'SiteConfigThreeStepWorkflow'
		)
	);
	
	static $extensionsToReapply = array();
	
	static function set_up_extensions() {
		// Save the state of existing extensions, then remove them
		foreach(self::$extensionsToRemove as $class => $extensions) {
			self::$extensionsToReapply[$class] = array();
			foreach($extensions as $extension) {
				if (singleton($class)->hasExtension($extension)) {
					self::$extensionsToReapply[$class][] = $extension;
					Object::remove_extension($class, $extension);
				}
			}
		}
		
		// Apply the ones needed for this test
		foreach(self::$extensionsToApply as $class => $extensions) {
			foreach($extensions as $extension) {
				Object::add_extension($class, $extension);
			}
		}
	}
	
	static function tear_down_extensions() {
		// Remove extensions added for testing
		foreach(self::$extensionsToApply as $class => $extensions) {
			foreach($extensions as $extension) {
				Object::remove_extension($class, $extension);
			}
		}
		
		// Reapply ones removed
		foreach(self::$extensionsToReapply as $class => $extensions) {
			foreach($extensions as $extension) {
				Object::add_extension($class, $extension);
			}
		}
	}
	
	static function set_up_once() {
		StaticPublisher::$disable_realtime = true;
		self::set_up_extensions();
		
		// clear singletons, they're caching old extension info which is used in DatabaseAdmin->doBuild()
		global $_SINGLETONS;
		$_SINGLETONS = array();

		// recreate database with new settings
		$dbname = self::create_temp_db();
		DB::set_alternative_database_name($dbname);
		
		parent::set_up_once();
	}
	
	static function tear_down_once() {
		self::tear_down_extensions();
		self::kill_temp_db();
		self::create_temp_db();
		parent::tear_down_once();
	}
		
	function testWorkflowPublicationApprovalTransition() {
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');
	
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
	
		// awaiting approval 
		$customauthor->logIn();
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
		
		$custompublisher->logIn();
	
		$request1->approve('Looks good');
	
		$this->assertEquals(
			$request1->Status,
			'Approved',
			"Request is set to Approved after page is approved"
		);
		
		$this->assertEquals(
			$request1->ApproverID,
			$custompublisher->ID,
			"Currently logged-in user is set as the Approver for this request"
		);
		
		$request1->publish('Avast, ye scoundrels!', $custompublisher, false);
		
		$this->assertEquals(
			$request1->Status,
			'Completed',
			"Request is set to Completed after page is published"
		);
		
		$this->assertEquals(
			$request1->PublisherID,
			$custompublisher->ID,
			"Currently logged-in user is set as the Publisher for this request"
		);
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
		
		SSDatetime::set_mock_now('2009-05-25 15:00:00');
		
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
		$sp->run(new HTTPRequest('GET', '/'));
		
		$this->assertEquals(
			$request->Status,
			'Scheduled',
			"Request is still set to Scheduled after approving a request with embargo and/or expriy dates set, and running the publisher cron"
		);
		
		SSDatetime::set_mock_now('2009-06-03 15:00:00');
		
		$sp->run(new HTTPRequest('GET', '/'));
		
		$request = DataObject::get_by_id('WorkflowPublicationRequest', $request->ID);
		
		$this->assertEquals(
			$request->Status,
			'Completed',
			"Request is Completed after embargo date set"
		);
		
		SSDatetime::set_mock_now('2009-06-15 15:00:00');
		$sp->run(new HTTPRequest('GET', '/'));
		
		$onLive = Versioned::get_by_stage('Page', 'Live', "SiteTree_Live.ID = ".$page->ID);
		$this->assertNull($onLive, 'Page has expired from live');
		
		SSDatetime::clear_mock_now();
	}
	
	function testBatchActions() {
		// Get fixtures
		$page1 = $this->objFromFixture('SiteTree', 'batchTest1');
		$page2 = $this->objFromFixture('SiteTree', 'batchTest2');
		$page3 = $this->objFromFixture('SiteTree', 'batchTest3');
		$page4 = $this->objFromFixture('SiteTree', 'batchTest4');
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
	
		// Modify content
		$page1->Title = rand();$page1->write();
		$page2->Title = rand();$page2->write();
		$page3->Title = rand();$page3->write();
		$page4->Title = rand();$page4->write();
	
		// Create WF requests for each of em
		$customauthor->logIn();
		$wf1 = $page1->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$wf2 = $page2->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$wf3 = $page3->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$wf4 = $page4->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		
		// // Create dataset
		$doSet = new DataObjectSet();
		$doSet->push($page1);
		$doSet->push($page2);
		$doSet->push($page3);
		$doSet->push($page4);
		
		// Batch approve
		$custompublisher->logIn();
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);
		$page1->batchApprove();
		$page2->batchApprove();
		$page3->batchApprove();
		$page4->batchApprove();
		$this->assertEquals($page1->openWorkflowRequest()->Status, 'Approved', 'Workflow status is approved after batch action');
		$this->assertEquals($page2->openWorkflowRequest()->Status, 'Approved', 'Workflow status is approved after batch action');
		$this->assertEquals($page3->openWorkflowRequest()->Status, 'Approved', 'Workflow status is approved after batch action');
		$this->assertEquals($page4->openWorkflowRequest()->Status, 'Approved', 'Workflow status is approved after batch action');

		// Batch publish
		$page1->batchPublish();
		$page2->batchPublish();
		$page3->batchPublish();
		$page4->batchPublish();
		$this->assertNull($page1->openWorkflowRequest(), 'No open workflow after publishing live');
		$this->assertNull($page2->openWorkflowRequest(), 'No open workflow after publishing live');
		$this->assertNull($page3->openWorkflowRequest(), 'No open workflow after publishing live');
		$this->assertNull($page4->openWorkflowRequest(), 'No open workflow after publishing live');
	}
}
?>