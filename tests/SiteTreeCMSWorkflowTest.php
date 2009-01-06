<?php
/**
 * @package cmsworkflow
 * @subpackage tests
 */
class SiteTreeCMSWorkflowTest extends FunctionalTest {
	
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
		$defaultpublisherspage = $this->objFromFixture('Page', 'defaultpublisherspage');
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
		$custompublisherspage = $this->objFromFixture('Page', 'custompublisherpage');
		$custompublishersgroup = $this->objFromFixture('Group', 'custompublishergroup');
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$custompublisher->Groups()->add($custompublishersgroup);
		$this->assertTrue(
			$custompublisherspage->canPublish($custompublisher),
			'Default publisher groups are assigned to new records'
		);
	}
	
	function testAccessTabOnlyDisplaysWithGrantAccessPermissions() {
		$page = $this->objFromFixture('Page', 'custompublisherpage');
		
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		$fields = $page->getCMSFields();
		$this->assertTrue(
			$fields->dataFieldByName('CanPublishType')->isReadonly(),
			'Users with publish or SITETREE_GRANT_ACCESS permission can change "publish" group assignments in cms fields'
		);
		
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);
		$fields = $page->getCMSFields();
		$this->assertFalse(
			$fields->dataFieldByName('CanPublishType')->isReadonly(),
			'Users without publish or SITETREE_GRANT_ACCESS permission cannot change "publish" group assignments in cms fields'
		);
		
		$this->session()->inst_set('loggedInAs', null);
	}
	
	function testCmsActionsLimited() {
		$custompublisherspage = $this->objFromFixture('Page', 'custompublisherpage');
		
		$custompublishersgroup = $this->objFromFixture('Group', 'custompublishergroup');
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$custompublisher->Groups()->add($custompublishersgroup);
		
		$customauthorsgroup = $this->objFromFixture('Group', 'customauthorsgroup');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		$customauthor->Groups()->add($customauthorsgroup);
		
		$unpublishedRecord = new Page();
		$unpublishedRecord->write();
		$unpublishedRecord->PublisherGroups()->add($custompublishersgroup);
		
		$publishedRecord = new Page();
		$publishedRecord->write();
		$publishedRecord->publish('Stage', 'Live');
		$publishedRecord->PublisherGroups()->add($custompublishersgroup);
		
		$deletedFromLiveRecord = new Page();
		$deletedFromLiveRecord->write();
		$deletedFromLiveRecord->publish('Stage', 'Live');
		$deletedFromLiveRecord->deleteFromStage('Live');
		$deletedFromLiveRecord->PublisherGroups()->add($custompublishersgroup);
		
		$deletedFromStageRecord = new Page();
		$deletedFromStageRecord->write();
		$deletedFromStageRecord->publish('Stage', 'Live');
		$deletedFromStageRecord->deleteFromStage('Stage');
		// @todo Workaround for datamodel flags not being set in the right places
		$deletedFromStageRecord->DeletedFromStage = true;
		$deletedFromStageRecord->PublisherGroups()->add($custompublishersgroup);
		
		$changedOnStageRecord = new Page();
		$changedOnStageRecord->write();
		$changedOnStageRecord->publish('Stage', 'Live');
		$changedOnStageRecord->Content = 'Changed on Stage';
		$changedOnStageRecord->write();
		$changedOnStageRecord->PublisherGroups()->add($custompublishersgroup);
		
		// test "publish" action for author
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		$this->assertNotContains(
			'action_publish',
			$unpublishedRecord->getCMSActions()->column('Name'),
			'Author cant trigger publish button'
		);
		$this->assertNotContains(
			'action_publish',
			$publishedRecord->getCMSActions()->column('Name'),
			'Author cant trigger publish button'
		);
		$this->assertNotContains(
			'action_publish',
			$deletedFromLiveRecord->getCMSActions()->column('Name'),
			'Author cant trigger publish button'
		);
		$this->assertNotContains(
			'action_publish',
			$changedOnStageRecord->getCMSActions()->column('Name'),
			'Author cant trigger publish button'
		);
		
		// test "publish" action for publisher
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);
		$this->assertContains(
			'action_publish',
			$unpublishedRecord->getCMSActions()->column('Name'),
			'Publisher can trigger publish button on unpublished pages'
		);
		$this->assertContains(
			'action_publish',
			$publishedRecord->getCMSActions()->column('Name'),
			'Publisher can trigger publish button on published pages'
		);
		$this->assertContains(
			'action_publish',
			$deletedFromLiveRecord->getCMSActions()->column('Name'),
			'Publisher can trigger publish button on published pages'
		);
		$this->assertContains(
			'action_publish',
			$changedOnStageRecord->getCMSActions()->column('Name'),
			'Publisher can trigger publish button on published pages'
		);
		
		// test "request publication" action for author
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		$this->assertNotContains(
			'action_cms_requestpublication',
			$unpublishedRecord->getCMSActions()->column('Name'),
			'Author cant trigger request publication button if page hasnt been altered'
		);
		$this->assertNotContains(
			'action_cms_requestpublication',
			$publishedRecord->getCMSActions()->column('Name'),
			'Author cant trigger request publication button if page has been published but not altered on stage'
		);
		$this->assertContains(
			'action_cms_requestpublication',
			$changedOnStageRecord->getCMSActions()->column('Name'),
			'Author can trigger request publication button if page has been changed on stage'
		);
		
		// test "request publication" action for publisher
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);
		$this->assertNotContains(
			'action_cms_requestpublication',
			$unpublishedRecord->getCMSActions()->column('Name'),
			'Publisher doesnt need request publication button'
		);
		$this->assertNotContains(
			'action_cms_requestpublication',
			$changedOnStageRecord->getCMSActions()->column('Name'),
			'Publisher doesnt need request publication button'
		);
		
		// test "delete from live" action for author
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		$this->assertNotContains(
			'action_deletefromlive',
			$deletedFromStageRecord->getCMSActions()->column('Name'),
			'Author cant trigger delete from live button on published pages'
		);
		
		// test "delete from live" action for publisher
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);
		$this->assertContains(
			'action_deletefromlive',
			$deletedFromStageRecord->getCMSActions()->column('Name'),
			'Publisher can trigger delete from live button on published pages'
		);
		
		// test "request removal" action for author
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		$this->assertNotContains(
			'action_cms_requestdeletefromlive',
			$unpublishedRecord->getCMSActions()->column('Name'),
			'Author cant trigger request removal button if page hasnt been altered'
		);
		$this->assertNotContains(
			'action_cms_requestdeletefromlive',
			$publishedRecord->getCMSActions()->column('Name'),
			'Author cant trigger request removal button if page has been published but not altered on stage'
		);
		$this->assertContains(
			'action_cms_requestdeletefromlive',
			$changedOnStageRecord->getCMSActions()->column('Name'),
			'Author cant trigger request removal button if page has been changed on stage but not deleted from stage'
		);
		$this->assertContains(
			'action_cms_requestdeletefromlive',
			$deletedFromStageRecord->getCMSActions()->column('Name'),
			'Author can trigger request removal button if page has been deleted from stage'
		);
		
		// test "request removal" action for publisher
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);
		$this->assertNotContains(
			'action_cms_requestdeletefromlive',
			$unpublishedRecord->getCMSActions()->column('Name'),
			'Publisher doesnt need request publication button'
		);
		$this->assertNotContains(
			'action_cms_requestdeletefromlive',
			$changedOnStageRecord->getCMSActions()->column('Name'),
			'Publisher doesnt need request removal button'
		);
	
		// reset login
		$this->session()->inst_set('loggedInAs', null);
	}
	
}
?>