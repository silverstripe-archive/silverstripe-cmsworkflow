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
	
	function testFake() {}
	
	// function setUp() {
	// 	parent::setUp();
	// 
	// 	// default records are not created in TestRunner by default
	// 	singleton('SiteTreeCMSWorkflow')->augmentDefaultRecords();
	// 	$fixtureFile = 'cmsworkflow/tests/SiteTreeCMSWorkflowTest.yml';
	// 	$fixture = new YamlFixture($fixtureFile);
	// 	$fixture->saveIntoDatabase();
	// 	// Duplicated to be compatible with 2.3 and 2.4
	// 	$this->fixtures = array($fixture);
	// 	$this->fixture = $fixture;
	// } 
	// 
	// function testAlternateCanPublishLimitsToPublisherGroups() {
	// 	// Check for default record group assignments
	// 	$defaultpublisherspage = $this->objFromFixture('SiteTree', 'defaultpublisherspage');
	// 	$defaultpublishersgroup = DataObject::get_one('Group', "Code = 'site-content-publishers'");
	// 	$defaultpublisher = $this->objFromFixture('Member', 'defaultpublisher');
	// 	
	// 	// Workaround because defaults aren't written in unit tests
	// 	$defaultpublisher->Groups()->add($defaultpublishersgroup);
	// 	
	// 	$gs = $defaultpublisher->Groups();
	// 	$this->assertTrue(
	// 		$defaultpublisherspage->canPublish($defaultpublisher),
	// 		'Default publisher groups are assigned to new records'
	// 	);
	// 	
	// 	// Check for random user publish permissions
	// 	$randomUser = $this->objFromFixture('Member', 'randomuser');
	// 	$this->assertFalse(
	// 		$defaultpublisherspage->canPublish($randomUser),
	// 		'Users which are not in publisher groups cant publish new pages'
	// 	);
	// 	
	// 	// Check for custom page group assignments
	// 	$custompublisherspage = $this->objFromFixture('SiteTree', 'custompublisherpage');
	// 	$custompublishersgroup = $this->objFromFixture('Group', 'custompublishergroup');
	// 	$custompublisher = $this->objFromFixture('Member', 'custompublisher');
	// 	$custompublisher->Groups()->add($custompublishersgroup);
	// 	$this->assertTrue(
	// 		$custompublisherspage->canPublish($custompublisher),
	// 		'Default publisher groups are assigned to new records'
	// 	);
	// }
	// 
	// function testAccessTabOnlyDisplaysWithGrantAccessPermissions() {
	// 	$page = $this->objFromFixture('SiteTree', 'custompublisherpage');
	// 	
	// 	$customauthor = $this->objFromFixture('Member', 'customauthor');
	// 	$this->session()->inst_set('loggedInAs', $customauthor->ID);
	// 	$fields = $page->getCMSFields();
	// 	$this->assertTrue(
	// 		$fields->dataFieldByName('CanPublishType')->isReadonly(),
	// 		'Users with publish or SITETREE_GRANT_ACCESS permission can change "publish" group assignments in cms fields'
	// 	);
	// 	
	// 	$custompublisher = $this->objFromFixture('Member', 'custompublisher');
	// 	$this->session()->inst_set('loggedInAs', $custompublisher->ID);
	// 	$fields = $page->getCMSFields();
	// 	$this->assertFalse(
	// 		$fields->dataFieldByName('CanPublishType')->isReadonly(),
	// 		'Users without publish or SITETREE_GRANT_ACCESS permission cannot change "publish" group assignments in cms fields'
	// 	);
	// 	
	// 	$this->session()->inst_set('loggedInAs', null);
	// }
	// 
	// function testCmsActionsLimited() {
	// 	// For 2.3 and 2.4 compatibility
	// 	$bt = defined('Database::USE_ANSI_SQL') ? "\"" : "`";
	// 
	// 	$custompublisherspage = $this->objFromFixture('SiteTree', 'custompublisherpage');
	// 	
	// 	$custompublishersgroup = $this->objFromFixture('Group', 'custompublishergroup');
	// 	$custompublisher = $this->objFromFixture('Member', 'custompublisher');
	// 	$custompublisher->Groups()->add($custompublishersgroup);
	// 	
	// 	$customauthorsgroup = $this->objFromFixture('Group', 'customauthorsgroup');
	// 	$customauthor = $this->objFromFixture('Member', 'customauthor');
	// 	$customauthor->Groups()->add($customauthorsgroup);
	// 	
	// 	$unpublishedRecord = new Page();
	// 	$unpublishedRecord->write();
	// 	$unpublishedRecord->PublisherGroups()->add($custompublishersgroup);
	// 	
	// 	$publishedRecord = new Page();
	// 	$publishedRecord->write();
	// 	$publishedRecord->doPublish();
	// 	$publishedRecord->PublisherGroups()->add($custompublishersgroup);
	// 	
	// 	$deletedFromLiveRecord = new Page();
	// 	$deletedFromLiveRecord->write();
	// 	$deletedFromLiveRecord->doPublish();
	// 	$deletedFromLiveRecord->deleteFromStage('Live');
	// 	$deletedFromLiveRecord->PublisherGroups()->add($custompublishersgroup);
	// 	
	// 	$deletedFromStageRecord = new Page();
	// 	$deletedFromStageRecord->write();
	// 	$deletedFromStageRecord->PublisherGroups()->add($custompublishersgroup);
	// 	$deletedFromStageRecord->doPublish();
	// 	$deletedFromStageRecordID = $deletedFromStageRecord->ID;
	// 	$deletedFromStageRecord->deleteFromStage('Stage');
	// 	$deletedFromStageRecord = Versioned::get_one_by_stage("SiteTree", "Live", "{$bt}SiteTree{$bt}.{$bt}ID{$bt} = $deletedFromStageRecordID");
	// 	
	// 	$changedOnStageRecord = new Page();
	// 	$changedOnStageRecord->write();
	// 	$changedOnStageRecord->publish('Stage', 'Live');
	// 	$changedOnStageRecord->Content = 'Changed on Stage';
	// 	$changedOnStageRecord->write();
	// 	$changedOnStageRecord->PublisherGroups()->add($custompublishersgroup);
	// 	
	// 	// test "publish" action for author
	// 	$this->session()->inst_set('loggedInAs', $customauthor->ID);
	// 	$this->assertNotContains(
	// 		'action_publish',
	// 		$unpublishedRecord->getCMSActions()->column('Name'),
	// 		'Author cant trigger publish button'
	// 	);
	// 	$this->assertNotContains(
	// 		'action_publish',
	// 		$publishedRecord->getCMSActions()->column('Name'),
	// 		'Author cant trigger publish button'
	// 	);
	// 	$this->assertNotContains(
	// 		'action_publish',
	// 		$deletedFromLiveRecord->getCMSActions()->column('Name'),
	// 		'Author cant trigger publish button'
	// 	);
	// 	$this->assertNotContains(
	// 		'action_publish',
	// 		$changedOnStageRecord->getCMSActions()->column('Name'),
	// 		'Author cant trigger publish button'
	// 	);
	// 	
	// 	// test "publish" action for publisher
	// 	$this->session()->inst_set('loggedInAs', $custompublisher->ID);
	// 	$this->assertContains(
	// 		'action_publish',
	// 		$unpublishedRecord->getCMSActions()->column('Name'),
	// 		'Publisher can trigger publish button on unpublished pages'
	// 	);
	// 	$this->assertContains(
	// 		'action_publish',
	// 		$publishedRecord->getCMSActions()->column('Name'),
	// 		'Publisher can trigger publish button on published pages'
	// 	);
	// 	$this->assertContains(
	// 		'action_publish',
	// 		$changedOnStageRecord->getCMSActions()->column('Name'),
	// 		'Publisher can trigger publish button on published pages'
	// 	);
	// 	
	// 	// test "request publication" action for author
	// 	$this->session()->inst_set('loggedInAs', $customauthor->ID);
	// 	$this->assertNotContains(
	// 		'action_cms_requestpublication',
	// 		$unpublishedRecord->getCMSActions()->column('Name'),
	// 		'Author cant trigger request publication button if page hasnt been altered'
	// 	);
	// 	$this->assertNotContains(
	// 		'action_cms_requestpublication',
	// 		$publishedRecord->getCMSActions()->column('Name'),
	// 		'Author cant trigger request publication button if page has been published but not altered on stage'
	// 	);
	// 	$this->assertContains(
	// 		'action_cms_requestpublication',
	// 		$changedOnStageRecord->getCMSActions()->column('Name'),
	// 		'Author can trigger request publication button if page has been changed on stage'
	// 	);
	// 	
	// 	// test "delete from live" action for author
	// 	$this->session()->inst_set('loggedInAs', $customauthor->ID);
	// 	$this->assertNotContains(
	// 		'action_deletefromlive',
	// 		$deletedFromStageRecord->getCMSActions()->column('Name'),
	// 		'Author cant trigger delete from live button on published pages'
	// 	);
	// 	
	// 	// test "delete from live" action for publisher
	// 	$this->session()->inst_set('loggedInAs', $custompublisher->ID);
	// 	$this->assertContains(
	// 		'action_deletefromlive',
	// 		$deletedFromStageRecord->getCMSActions()->column('Name'),
	// 		'Publisher can trigger delete from live button on published pages'
	// 	);
	// 	
	// 	// test "request removal" action for author
	// 	$this->session()->inst_set('loggedInAs', $customauthor->ID);
	// 	$this->assertNotContains(
	// 		'action_cms_requestdeletefromlive',
	// 		$unpublishedRecord->getCMSActions()->column('Name'),
	// 		'Author cant trigger request removal button if page hasnt been altered'
	// 	);
	// 	$this->assertNotContains(
	// 		'action_cms_requestdeletefromlive',
	// 		$publishedRecord->getCMSActions()->column('Name'),
	// 		'Author cant trigger request removal button if page has been published but not altered on stage'
	// 	);
	// 	$this->assertNotContains(
	// 		'action_cms_requestdeletefromlive',
	// 		$changedOnStageRecord->getCMSActions()->column('Name'),
	// 		'Author cant trigger request removal button if page has been changed on stage but not deleted from stage'
	// 	);
	// 	$this->assertContains(
	// 		'action_cms_requestdeletefromlive',
	// 		$deletedFromStageRecord->getCMSActions()->column('Name'),
	// 		'Author can trigger request removal button if page has been deleted from stage'
	// 	);
	// 	
	// 	// test "request removal" action for publisher
	// 	$this->session()->inst_set('loggedInAs', $custompublisher->ID);
	// 	$this->assertNotContains(
	// 		'action_cms_requestdeletefromlive',
	// 		$unpublishedRecord->getCMSActions()->column('Name'),
	// 		'Publisher doesnt need request publication button'
	// 	);
	// 	$this->assertNotContains(
	// 		'action_cms_requestdeletefromlive',
	// 		$changedOnStageRecord->getCMSActions()->column('Name'),
	// 		'Publisher doesnt need request removal button'
	// 	);
	// 
	// 	// reset login
	// 	$this->session()->inst_set('loggedInAs', null);
	// }
}
?>