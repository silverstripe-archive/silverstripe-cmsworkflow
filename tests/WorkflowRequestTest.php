<?php
/**
 * @package cmsworkflow
 * @subpackage tests
 */
class WorkflowRequestTest extends FunctionalTest {
	
	static $fixture_file = 'cmsworkflow/tests/SiteTreeCMSWorkflowTest.yml';

	protected $requiredExtensions = array(
		'SiteTree' => array('SiteTreeCMSTwoStepWorkflow'),
		'SiteConfig' => array('SiteConfigTwoStepWorkflow'),
		'WorkflowRequest' => array('WorkflowTwoStepRequest'),
	);

	protected $illegalExtensions = array(
		'SiteTree' => array('SiteTreeCMSThreeStepWorkflow'),
		'WorkflowRequest' => array('WorkflowThreeStepRequest'),
		'LeftAndMain' => array('LeftAndMainCMSThreeStepWorkflow'),
		'SiteConfig' => array('SiteConfigThreeStepWorkflow'),
	);
	
	protected $requireDefaultRecordsFrom = array(
		'WorkflowSystemMember'
	);

	
	function setUp() {
		// Static publishing will just confuse things
		StaticPublisher::$disable_realtime = true;
		parent::setUp();
	}
	
	function tearDown() {
		parent::tearDown();
		// Static publishing will just confuse things
		StaticPublisher::$disable_realtime = false;
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
}
?>
