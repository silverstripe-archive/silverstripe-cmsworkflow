<?php
/**
 * @package cmsworkflow
 * @subpackage tests
 */
class SiteTreeCMSWorkflowTest extends SapphireTest {
	
	/**
	 * Created in setUp() to ensure defaults are created *before* inserting new fixtures,
	 * as they rely on certain default groups being present.
	 */
	//static $fixture_file = 'cmsworkflow/tests/SiteTreeCMSWorkflowTest.yml';
	
	function setUp() {
		parent::setUp();

		// default records are not created in TestRunner by default
		singleton('SiteTreeCMSWorkflow')->augmentDefaultRecords();
		$fixtureFile = 'cmsworkflow/tests/SiteTreeCMSWorkflowTest.yml';
		$this->fixture = new YamlFixture($fixtureFile);
		$this->fixture->saveIntoDatabase();
	} 
	
	function testAlternateCanPublishLimitsToPublisherGroups() {
		// Check for default record group assignments
		$defaultpublisherspage = $this->objFromFixture('SiteTree', 'defaultpublisherspage');
		$defaultpublishersgroup = DataObject::get_one('Group', "Code = 'site-content-publishers'");
		$defaultpublisher = $this->objFromFixture('Member', 'defaultpublisher');
		
		// Workaround because defaults aren't written in unit tests
		$defaultpublisher->Groups()->add($defaultpublishersgroup);
		
		$gs = $defaultpublisher->Groups();
		$this->assertTrue(
			$defaultpublisherspage->canPublish($defaultpublisher),
			'Default publisher groups are assigned to new records'
		);
		
		// Check for random user publish permissions
		$randomUser = $this->objFromFixture('Member', 'randomuser');
		$this->assertFalse(
			$defaultpublisherspage->canPublish($randomUser),
			'Users which are not in publisher groups cant publish new pages'
		);
		
		// Check for custom page group assignments
		$custompublisherspage = $this->objFromFixture('SiteTree', 'custompublisherpage');
		$custompublishersgroup = $this->objFromFixture('Group', 'custompublishergroup');
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$custompublisher->Groups()->add($custompublishersgroup);
		$this->assertTrue(
			$custompublisherspage->canPublish($custompublisher),
			'Default publisher groups are assigned to new records'
		);
	}
	
}
?>