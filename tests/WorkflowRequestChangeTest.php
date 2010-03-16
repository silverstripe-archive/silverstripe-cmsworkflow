<?php
/**
 * @package cmsworkflow
 * @subpackage tests
 */
class WorkflowRequestChangeTest extends FunctionalTest {
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
		
	function testChangesAreTracked() {
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');

		$custompublishersgroup = $this->objFromFixture('Group', 'custompublishergroup');
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$custompublisher->Groups()->add($custompublishersgroup);

		$customauthorsgroup = $this->objFromFixture('Group', 'customauthorsgroup');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		$customauthor->Groups()->add($customauthorsgroup);
		
		$page->PublisherGroups()->add($custompublishersgroup);
		
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		$page->Content = 'edited';
		$page->write();
		
		$request = WorkflowPublicationRequest::create_for_page($page);
		$request->request("Please publish this");
		
		// We need to sleep a little here to let the time tick up a bit 
		sleep(1.25);
		
		$this->assertEquals(
			1,
			$request->Changes()->Count(),
			'Change has been tracked for initial publication request'
		);
		$page->write();
		$this->assertEquals(
			1,
			$request->Changes()->Count(),
			'Changes arent tracked twice without a Status change'
		);
		$change = $request->Changes()->First();
		$this->assertEquals(
			$change->AuthorID,
			$customauthor->ID,
			"Change has the correct author assigned"
		);
		$this->assertEquals(
			$change->PageDraftVersion,
			$page->Version,
			"Change has the correct draft version"
		);
		
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);
		$page->Content = "third";
		$page->write();
		$page->doPublish();
		$request->flushCache();
		$this->assertEquals(
			2,
			$request->Changes()->Count(),
			'Change has been tracked for the publication step'
		);
		$change = $request->Changes()->Last();
		$this->assertEquals(
			$change->AuthorID,
			$custompublisher->ID,
			"Change has the correct author assigned"
		);
		$this->assertEquals(
			$page->Version,
			$change->PageLiveVersion,
			"Change has the corrent draft version"
		);
		$page->doPublish();
		$page->doPublish();
		$page->doPublish();
		
		$changes = $request->Changes()->toArray();

		$firstChange = $changes[0];
		$secondChange = $changes[1];
		
		$this->assertTrue($firstChange->NextChange()->ID == $secondChange->ID);
		$this->assertTrue($secondChange->PreviousChange()->ID == $firstChange->ID);
		
		$this->assertEquals($firstChange->getStatusDescription(), 'Awaiting Approval');
		$this->assertEquals($firstChange->getDiffLinkToLastPublished(), 'admin/compareversions/2/?From=2&To=3');
		$this->assertEquals($secondChange->getDiffLinkToLastPublished(), 'admin/compareversions/2/?From=3&To=3');
		$this->assertEquals($secondChange->getDiffLinkToOriginalRequest(), 'admin/compareversions/2/?From=2&To=3');
		$this->assertEquals($secondChange->getDiffLinkOriginalToLastPublished(), 'admin/compareversions/2/?From=3&To=3');
		$this->assertEquals($secondChange->getDiffLinkToPrevious(), 'admin/compareversions/2/?From=2&To=3');
		$this->assertEquals($secondChange->getDiffLinkContentToPrevious(), '<a href="admin/compareversions/2/?From=2&To=3" target="_blank" class="externallink">Show</a>');
		
		
		
		$this->session()->inst_set('loggedInAs', null);
	}
}
?>