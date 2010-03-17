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
		
		$this->assertContains($this->objFromFixture('SiteTree', 'page1')->ID, 
			$report->sourceRecords(array())->column('ID'));
		$this->assertContains($this->objFromFixture('SiteTree', 'page2')->ID, 
			$report->sourceRecords(array())->column('ID'));
	}

}
?>