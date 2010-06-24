<?php
/**
 * @package cmsworkflow
 * @subpackage tests
 */
class TwoStepWorkflowReportsTest extends FunctionalTest {
	
	static $fixture_file = 'cmsworkflow/tests/TwoStepWorkflowReportsTest.yml';

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
	
	function testDeletionRequestsINeedToApproveReport() {
		$report = new SideReportWrapper('UnapprovedDeletions2StepReport');
		$this->assertTrue(is_string($report->title()));
		$this->assertTrue(is_string($report->group()));
		$this->assertTrue(is_numeric($report->sort()));
		$this->assertTrue(is_array($report->columns()));
		$this->assertTrue(is_array(singleton('UnapprovedDeletions2StepReport')->columns()));
		$this->assertTrue($report->canView());
		
		$publisher = $this->objFromFixture('Member', 'publisher');
		$admin = $this->objFromFixture('Member', 'admin');
		
		$this->logInAs($publisher);
		$results = $report->sourceRecords(array());
		$this->assertContains($this->objFromFixture('SiteTree', 'page2')->ID, $results->column('ID'));
		$this->assertEquals(1, $results->Count());
		
		$this->logInAs($admin);
		$this->assertEquals(2, $report->sourceRecords(array())->Count());
	}
	
	function testPublicationRequestsINeedToApproveReport() {
		$report = new SideReportWrapper('UnapprovedPublications2StepReport');
		$this->assertTrue(is_string($report->title()));
		$this->assertTrue(is_string($report->group()));
		$this->assertTrue(is_numeric($report->sort()));
		$this->assertTrue(is_array($report->columns()));
		$this->assertTrue(is_array(singleton('UnapprovedPublications2StepReport')->columns()));
		$this->assertTrue($report->canView());
		
		$publisher = $this->objFromFixture('Member', 'publisher');
		$admin = $this->objFromFixture('Member', 'admin');
		
		$this->logInAs($publisher);
		$results = $report->sourceRecords(array());
		$this->assertContains($this->objFromFixture('SiteTree', 'page1')->ID, $results->column('ID'));
		
		$this->logInAs($admin);
		$this->assertEquals(2, $report->sourceRecords(array())->Count());
	}
	
	function testMyRequestsPendingReview() {
		$report = new MyTwoStepWorkflowRequests();
		$this->assertTrue(is_string($report->title()));
		$this->assertTrue(is_string($report->group()));
		$this->assertTrue(is_numeric($report->sort()));
		$this->assertTrue(is_array($report->columns()));
		$this->assertTrue($report->canView());
		
		$author = $this->objFromFixture('Member', 'author');
		$this->logInAs($author);
		$page1 = $this->objFromFixture('SiteTree', 'page1');
		$page3 = $this->objFromFixture('SiteTree', 'page3');

		// Note: The intention is that we want to see pages 1 through 4
		// in the sourceRecords. In practice at the moment we don't actually
		// see page 2 or page 4 (the deletion requests), because the yaml
		// is not set up right at present - these don't exist in draft,
		// but they are expected to exist in live where we can join them.
		// @todo fix yaml so that deleted pages are not present in draft,
		// but are present in live.
		$sourceRecords = $report->sourceRecords(array());
		$this->assertContains($page1->ID, 
			$sourceRecords->column('ID'));
		$this->assertContains($page3->ID, 
			$sourceRecords->column('ID'));
	}

}
?>