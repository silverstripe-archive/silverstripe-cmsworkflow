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
			'SiteTreeCMSTwoStepWorkflow'
		), 'WorkflowRequest' => array(
			'WorkflowTwoStepRequest'
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
	
	function setUp() {
		parent::setUp();

		// default records are not created in TestRunner by default
		singleton('SiteTreeCMSTwoStepWorkflow')->augmentDefaultRecords();
		$fixtureFile = 'cmsworkflow/tests/SiteTreeCMSWorkflowTest.yml';
		$fixture = new YamlFixture($fixtureFile);
		$fixture->saveIntoDatabase();
		// Duplicated to be compatible with 2.3 and 2.4
		$this->fixtures = array($fixture);
		$this->fixture = $fixture;
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
	
	function testAccessTabOnlyDisplaysWithGrantAccessPermissions() {
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');
		
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
		// For 2.3 and 2.4 compatibility
		$bt = defined('SS_SS_Database::USE_ANSI_SQL') ? "\"" : "`";

		$custompublisherspage = $this->objFromFixture('SiteTree', 'custompublisherpage');
		
		$custompublishersgroup = $this->objFromFixture('Group', 'custompublishergroup');
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$workflowadmin = $this->objFromFixture('Member', 'workflowadmin');
		$custompublisher->Groups()->add($custompublishersgroup);
		
		$customauthorsgroup = $this->objFromFixture('Group', 'customauthorsgroup');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		$customauthor->Groups()->add($customauthorsgroup);
		
		$unpublishedRecord = new Page();
		$unpublishedRecord->CanEditType = 'LoggedInUsers';
		$unpublishedRecord->write();
		$unpublishedRecord->PublisherGroups()->add($custompublishersgroup);
		
		$publishedRecord = new Page();
		$publishedRecord->CanEditType = 'LoggedInUsers';
		$publishedRecord->write();
		$publishedRecord->doPublish();
		$publishedRecord->PublisherGroups()->add($custompublishersgroup);
		
		$deletedFromLiveRecord = new Page();
		$deletedFromLiveRecord->CanEditType = 'LoggedInUsers';
		$deletedFromLiveRecord->write();
		$deletedFromLiveRecord->doPublish();
		$deletedFromLiveRecord->deleteFromStage('Live');
		$deletedFromLiveRecord->PublisherGroups()->add($custompublishersgroup);
		
		$deletedFromStageRecord = new Page();
		$deletedFromStageRecord->CanEditType = 'LoggedInUsers';
		$deletedFromStageRecord->write();
		$deletedFromStageRecord->PublisherGroups()->add($custompublishersgroup);
		$deletedFromStageRecord->doPublish();
		$deletedFromStageRecordID = $deletedFromStageRecord->ID;
		$deletedFromStageRecord->deleteFromStage('Stage');
		$deletedFromStageRecord = Versioned::get_one_by_stage("SiteTree", "Live", "{$bt}SiteTree{$bt}.{$bt}ID{$bt} = $deletedFromStageRecordID");
		
		$changedOnStageRecord = new Page();
		$changedOnStageRecord->CanEditType = 'LoggedInUsers';
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
		$this->assertNotContains(
			'action_publish',
			$unpublishedRecord->getCMSActions()->column('Name'),
			'Publisher cant trigger publish button'
		);
		$this->assertNotContains(
			'action_publish',
			$publishedRecord->getCMSActions()->column('Name'),
			'Publisher cant trigger publish button'
		);
		$this->assertNotContains(
			'action_publish',
			$changedOnStageRecord->getCMSActions()->column('Name'),
			'Publisher cant trigger publish button'
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
		$this->assertNotContains(
			'action_cms_requestdeletefromlive',
			$changedOnStageRecord->getCMSActions()->column('Name'),
			'Author cant trigger request removal button if page has been changed on stage but not deleted from stage'
		);
		
		// test "request removal" action for publisher
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);

		// reset login
		$this->session()->inst_set('loggedInAs', null);
	}
}
?>