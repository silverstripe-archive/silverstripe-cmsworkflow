<?php
/**
 * These tests test the reports that are available on
 * two and three step. Though we are testing it using
 * two step.
 *
 * @package cmsworkflow
 * @subpackage tests
 */
class WorkflowReportsTest extends FunctionalTest {
	
	static $fixture_file = 'cmsworkflow/tests/WorkflowReportsTest.yml';

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
	
	function testPagesScheduledForPublishingReport() {
		$report = new PagesScheduledForPublishingReport();
		$this->assertTrue(is_string($report->title()));
		$this->assertTrue(is_array($report->columns()));
		$this->assertTrue($report->canView());
		$this->assertTrue($report->parameterFields() instanceof FieldSet);
		
		$this->logInAs($this->objFromFixture('Member', 'admin'));
		
		// Test with no dates set
		SS_Datetime::set_mock_now('2010-02-14 00:00:00');
		$results = $report->sourceRecords(array(), '"Title" DESC', false);
		$this->assertEquals($results->column('Title'), array(
			'Page3',
			'Page2'
		));
		
		// Test with start date only
		$results = $report->sourceRecords(array(
			'StartDate' => array(
				'Date' => '14/02/2010',
				'Time' => '12:00 am'
			)
		), 'Title DESC', false);
		$this->assertEquals($results->column('Title'), array(
			'Page3',
			'Page2'
		));
		
		// Test with end date only
		$results = $report->sourceRecords(array(
			'EndDate' => array(
				'Date' => '14/02/2010',
				'Time' => '12:00 am'
			)
		), 'Title ASC', false);
		$this->assertEquals($results->column('Title'), array(
			'Page1'
		));
		
		// Test with start and end dates
		$results = $report->sourceRecords(array(
			'StartDate' => array(
				'Date' => '04/02/2010',
				'Time' => '12:00 am'
			),
			'EndDate' => array(
				'Date' => '12/02/2010',
				'Time' => '12:00 am'
			)
		), 'AbsoluteLink DESC', false);
		$this->assertEquals($results->column('Title'), array(
			'Page1'
		));
		
		// Test that records you cannot edit do not appear
		SS_Datetime::set_mock_now('2010-02-01 00:00:00');
		$this->logInAs($this->objFromFixture('Member', 'admin'));
		$this->assertEquals($report->sourceRecords(array(), '"Title" DESC', false)->Count(), 3);
		$this->logInAs($this->objFromFixture('Member', 'publisher'));
		$this->assertEquals($report->sourceRecords(array(), '"Title" DESC', false)->Count(), 2);
		
		SS_Datetime::clear_mock_now();
	}
	
	function testPagesScheduledForDeletionReport() {
		$report = new PagesScheduledForDeletionReport();
		$this->assertTrue(is_string($report->title()));
		$this->assertTrue(is_array($report->columns()));
		$this->assertTrue($report->canView());
		$this->assertTrue($report->parameterFields() instanceof FieldSet);
		
		$this->logInAs($this->objFromFixture('Member', 'admin'));
		
		$this->objFromFixture('SiteTree', 'pagedel1')->doPublish();
		$this->objFromFixture('SiteTree', 'pagedel2')->doPublish();
		$this->objFromFixture('SiteTree', 'pagedel3')->doPublish();

		// Test with no dates set
		SS_Datetime::set_mock_now('2010-02-14 00:00:00');
		$results = $report->sourceRecords(array(), '"Title" DESC', false);
		$this->assertEquals($results->column('Title'), array(
			'Page3',
			'Page2'
		));
		
		// Test with start date only
		$results = $report->sourceRecords(array(
			'StartDate' => array(
				'Date' => '14/02/2010',
				'Time' => '12:00 am'
			)
		), 'Title DESC', false);
		$this->assertEquals($results->column('Title'), array(
			'Page3',
			'Page2'
		));
		
		// Test with end date only
		$results = $report->sourceRecords(array(
			'EndDate' => array(
				'Date' => '14/02/2010',
				'Time' => '12:00 am'
			)
		), 'Title ASC', false);
		$this->assertEquals($results->column('Title'), array(
			'Page1'
		));
		
		// Test with start and end dates
		$results = $report->sourceRecords(array(
			'StartDate' => array(
				'Date' => '04/02/2010',
				'Time' => '12:00 am'
			),
			'EndDate' => array(
				'Date' => '12/02/2010',
				'Time' => '12:00 am'
			)
		), 'Title DESC', false);
		$this->assertEquals($results->column('Title'), array(
			'Page1'
		));
		
		// Test that records you cannot edit do not appear
		SS_Datetime::set_mock_now('2010-02-01 00:00:00');
		$this->logInAs($this->objFromFixture('Member', 'admin'));
		$this->assertEquals($report->sourceRecords(array(), '', false)->Count(), 3);
		$this->logInAs($this->objFromFixture('Member', 'publisher'));
		$this->assertEquals($report->sourceRecords(array(), '"Title" DESC', false)->Count(), 2);
		
		SS_Datetime::clear_mock_now();
	}
	
	function testRecentlyPublishedPagesReport() {
		$report = new RecentlyPublishedPagesReport();
		$this->assertTrue(is_string($report->title()));
		$this->assertTrue(is_array($report->columns()));
		$this->assertTrue($report->canView());
		$this->assertTrue($report->parameterFields() instanceof FieldSet);
		
		$this->logInAs($this->objFromFixture('Member', 'admin'));
		
		SS_Datetime::set_mock_now('2010-02-10 15:00:00');
		$page1 = new Page();
		$page1->Title = 'Page1';
		$page1->write();
		$wfr = $page1->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$wfr->request('Request');
		$wfr->approve('Approved');
		SS_Datetime::set_mock_now('2010-02-15 15:00:00');
		$page2 = new Page();
		$page2->Title = 'Page2';
		$page2->write();
		$wfr = $page2->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$wfr->request('Request');
		$wfr->approve('Approved');
		SS_Datetime::set_mock_now('2010-02-16 15:00:00');
		$page3 = new Page();
		$page3->Title = 'Page3';
		$page3->write();
		$wfr = $page3->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$wfr->request('Request');
		$wfr->approve('Approved');
		
		SS_Datetime::set_mock_now('2010-02-14 00:00:00');
		// Test with no dates set
		$results = $report->sourceRecords(array(), '"Title" DESC', false);//die();
		$this->assertEquals($results->column('Title'), array(
			'Page3',
			'Page2'
		));
		
		// Test with start date only
		$results = $report->sourceRecords(array(
			'StartDate' => array(
				'Date' => '14/02/2010',
				'Time' => '12:00 am'
			)
		), '"Title" DESC', false);
		$this->assertEquals($results->column('Title'), array(
			'Page3',
			'Page2'
		));
		
		// Test with end date only
		$results = $report->sourceRecords(array(
			'EndDate' => array(
				'Date' => '14/02/2010',
				'Time' => '12:00 am'
			)
		), '"Title" ASC', false);
		$this->assertEquals($results->column('Title'), array(
			'Page1'
		));
		
		// Test with start and end dates
		$results = $report->sourceRecords(array(
			'StartDate' => array(
				'Date' => '04/02/2010',
				'Time' => '12:00 am'
			),
			'EndDate' => array(
				'Date' => '12/02/2010',
				'Time' => '12:00 am'
			)
		), '"Title" DESC', false);
		$this->assertEquals($results->column('Title'), array(
			'Page1'
		));
		
		SS_Datetime::clear_mock_now();
	}
}
?>