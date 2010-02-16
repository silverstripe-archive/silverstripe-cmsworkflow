<?php

/**
 * Tests the functionality for previewing the future state of the site.
 */
class SiteTreeFutureStateTest extends SapphireTest {
	static $fixture_file = 'cmsworkflow/tests/SiteTreeFutureStateTest.yml';

	function testPagesWithBothEmbargoAndExpiryAreDisplayedCorrectlyInFutureState() {
		Versioned::reading_stage('Stage');
		
		$product5 = $this->objFromFixture('Page', 'embargotest');
		
		$product5->publish('Stage', 'Live');
		
		$product5->Title = 'New Title';
		$product5->write();
		$request = $product5->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$product5->setEmbargo('01/06/2020', '3:00pm');
		$product5->setExpiry('07/06/2020', '3:00pm');
		$product5->write();
		
		$request = $product5->openWorkflowRequest('WorkflowPublicationRequest');

		$request->approve('Looks good.');
		
		$prodDraft = DataObject::get_one('SiteTree', 'URLSegment = \'product-5\'');
		$this->assertEquals($prodDraft->Title, 'New Title', 'Correct page on draft site.');
		
		$prodLiveNow = Versioned::get_one_by_stage('SiteTree', 'Live', 'URLSegment = \'product-5\'');
		$this->assertEquals($prodLiveNow->Title, 'Product 5', 'Correct page on live site.');
		
		SiteTreeFutureState::set_future_datetime('2020-06-01 14:00:00');
		$prodBeforeEmbargo = DataObject::get_one('SiteTree', 'URLSegment = \'product-5\'');
		$this->assertEquals($prodBeforeEmbargo->Title, 'Product 5', 'Correct page before embargo.');
		
		SiteTreeFutureState::set_future_datetime('2020-06-02 16:00:00');
		$prodAfterEmbargo = DataObject::get_one('SiteTree', 'URLSegment = \'product-5\'');
		$this->assertEquals($prodAfterEmbargo->Title, 'New Title', 'Correct page after embargo.');
		

		SiteTreeFutureState::set_future_datetime('2020-06-07 16:00:00');
		$prodAfterExpiry = DataObject::get_one('SiteTree', 'URLSegment = \'product-5\'');
		$this->assertFalse($prodAfterExpiry, 'No page after expiry.');
		
		
		Versioned::reading_stage('Live');
		
	}

	function testTopLevelPagesArentAffectedByEmbargoedChildren() {
		// The top-level items have no embargo/expiry, and so should be unaffected by the embargoes
		// of their children
		
		$items1 = DataObject::get("SiteTree", "ParentID = 0 AND ShowInMenus = 1")->column("Title");
		SiteTreeFutureState::set_future_datetime('2020-01-01 10:00:00');
		$items2 = DataObject::get("SiteTree", "ParentID = 0 AND ShowInMenus = 1")->column("Title");
		SiteTreeFutureState::set_future_datetime('2020-01-01 10:59:00');
		$items3 = DataObject::get("SiteTree", "ParentID = 0 AND ShowInMenus = 1")->column("Title");
		SiteTreeFutureState::set_future_datetime('2020-01-01 11:01:00');
		$items4 = DataObject::get("SiteTree", "ParentID = 0 AND ShowInMenus = 1")->column("Title");
		SiteTreeFutureState::set_future_datetime('2020-01-03 11:01:00');
		$items5 = DataObject::get("SiteTree", "ParentID = 0 AND ShowInMenus = 1")->column("Title");
		
		$this->assertEquals(array('Home', 'About Us', 'Products', 'Contact Us'), $items1);
		$this->assertEquals(array('Home', 'About Us', 'Products', 'Contact Us'), $items2);
		$this->assertEquals(array('Home', 'About Us', 'Products', 'Contact Us'), $items3);
		$this->assertEquals(array('Home', 'About Us', 'Products', 'Contact Us'), $items4);
		$this->assertEquals(array('Home', 'About Us', 'Products', 'Contact Us'), $items5);
	}


	function testEmbargoAndExpiryAffectsRegularDataObjectRequests() {
		$products = $this->objFromFixture('Page', 'products');
		
		// The top-level items have no embargo/expiry, and so should be unaffected by the embargoes
		// of their children
		
		$this->assertEquals(array('Product 1', 'Product 2','Product 5'),
			$products->Children()->column("Title"));

		// Hasn't changed 1 minute before
		SiteTreeFutureState::set_future_datetime('2020-01-01 09:59:00');
		$this->assertEquals(array('Product 1', 'Product 2','Product 5'),
			$products->Children()->column("Title"));

		// Product 4 appears on exactly its embargo date
		SiteTreeFutureState::set_future_datetime('2020-01-01 10:00:00');
		$products->flushCache();
		$this->assertEquals(array('Product 1', 'Product 2', 'Product 4','Product 5'),
			$products->Children()->column("Title"));

		// Product 2 disappears on exactly its expiry date
		SiteTreeFutureState::set_future_datetime('2020-01-01 10:59:00');
		$products->flushCache();
		$this->assertEquals(array('Product 1', 'Product 2', 'Product 4','Product 5'),
			$products->Children()->column("Title"));
		SiteTreeFutureState::set_future_datetime('2020-01-01 11:00:00');
		$products->flushCache();
		$this->assertEquals(array('Product 1', 'Product 4', 'Product 5'),
			$products->Children()->column("Title"));

		SiteTreeFutureState::set_future_datetime('2020-01-03 11:01:00');
		$products->flushCache();
		$this->assertEquals(array('Product 1', 'Product 3', 'Product 4','Product 5'),
			$products->Children()->column("Title"));
	}
	
	/**
	 * Test that the 404 page can be found, which also tests that subclass fields work on future
	 * state view.
	 */
	function test404PageOnFutureState() {
		SiteTreeFutureState::set_future_datetime('2020-01-01 09:59:00');
		
		$errorPage = DataObject::get_one("ErrorPage", "\"ErrorCode\" = '404'");

		$this->assertType('ErrorPage', $errorPage);
		$this->assertEquals("Page not Found", $errorPage->Title);
	}
	
	function testGetOneByStageInFutureState() {
		$about = $this->objFromFixture('Page', 'about');
		Versioned::reading_stage('Stage');
		$about->Title = "New About Us";
		$about->write();
		Versioned::reading_stage('Live');
		
		SiteTreeFutureState::set_future_datetime('2020-01-03 11:01:00');

		$aboutStage = Versioned::get_one_by_stage("SiteTree", "Stage", "\"SiteTree\".\"ID\" = '$about->ID'");
		$aboutLive = Versioned::get_one_by_stage("SiteTree", "Live", "\"SiteTree\".\"ID\" = '$about->ID'");
		
		$this->assertEquals('New About Us', $aboutStage->Title);
		$this->assertEquals('About Us', $aboutLive->Title);
	}
	
	/**
	 * Test virtual pages for future state
	 */
	function testVirtualPageFutureState() {
		$virtuals = $this->objFromFixture('Page', 'virtuals');

		$this->assertEquals(array('Product 1', 'Product 2', 'Product 5'),
			$virtuals->Children()->column("Title"));

		// Test embargo - the draft-only VP, Product 4, is *not* auto-published
		SiteTreeFutureState::set_future_datetime('2020-01-01 10:30:00');
		$virtuals->flushCache();

		$this->assertEquals(array('Product 1', 'Product 2', 'Product 5'),
			$virtuals->Children()->column("Title"));

		// Test expiry - Product 2 is auto-removed
		SiteTreeFutureState::set_future_datetime('2020-01-01 11:30:00');
		$virtuals->flushCache();

		$this->assertEquals(array('Product 1', 'Product 5'),
			$virtuals->Children()->column("Title"));
		
	}
	
	/**
	 * Test that an expiry date set after the virtual page is created is respected in the tes
	 */
	function testExpirySetAfterVirtualPageCreated() {
		Versioned::reading_stage('Stage');
		$p5 = $this->objFromFixture('Page', 'product5');

		// Create an expiry following the workflow process
		$req = $p5->openOrNewWorkflowRequest('WorkflowDeletionRequest');

		// Todo - remove UI<->model coupling
		$_REQUEST['DeletionScheduling'] = 'scheduled';
		$_REQUEST['ExpiryDate']['Date'] = '01/01/2020';
		$_REQUEST['ExpiryDate']['Time'] = '12:00';
		$req->approve('Schedule the deletion');

		Versioned::reading_stage('Live');
		
		$pages = DataObject::get("SiteTree")->column("ID");
		$this->assertContains($this->idFromFixture('Page', 'product5'), $pages);
		$this->assertContains($this->idFromFixture('VirtualPage', 'vproduct5'), $pages);
		
		SiteTreeFutureState::set_future_datetime('2020-01-01 9:00:00');

		$pages = DataObject::get("SiteTree")->column("ID");
		$this->assertContains($this->idFromFixture('Page', 'product5'), $pages);
		$this->assertContains($this->idFromFixture('VirtualPage', 'vproduct5'), $pages);

		SiteTreeFutureState::set_future_datetime('2020-01-01 14:00:00');

		$pages = DataObject::get("SiteTree")->column("ID");
		$this->assertNotContains($this->idFromFixture('Page', 'product5'), $pages);
		$this->assertNotContains($this->idFromFixture('VirtualPage', 'vproduct5'), $pages);
		
	}

	/**
	 * Test embargo edits for both regular and virtual pages.
	 */
	function testEmbargoedEdit() {
		// Make an edit
		Versioned::reading_stage('Stage');
		
		$product1 = $this->objFromFixture('Page', 'product1');
		$product1->Title = "New Product 1";
		$product1->write();

		// Embargo the change
		$wf = $product1->openOrNewWorkflowRequest('WorkflowPublicationRequest');
		$wf->EmbargoDate = '2019-01-01 10:00:00';
		$wf->approve("Approved");

		// Verify that the change isn't currently on the live site (just in case a bug meant that
		// the change was insta-published
		Versioned::reading_stage('Live');
		singleton('Page')->flushCache();

		$p1 = $this->objFromFixture('Page', 'product1');
		$vp1 = $this->objFromFixture('VirtualPage', 'vproduct1');

		$this->assertEquals('Product 1', $p1->Title);
		$this->assertEquals('Product 1', $vp1->Title);

		// Verify the the change isn't reflected in future state prior to the embargo date
		SiteTreeFutureState::set_future_datetime('2019-01-01 9:30:00');
		singleton('Page')->flushCache();

		$p1 = $this->objFromFixture('Page', 'product1');
		$vp1 = $this->objFromFixture('VirtualPage', 'vproduct1');

		$this->assertEquals('Product 1', $p1->Title);
		$this->assertEquals('Product 1', $vp1->Title);

		// Verify the the change is reflected in both the source page and its virtual
		SiteTreeFutureState::set_future_datetime('2019-01-01 10:30:00');
		singleton('Page')->flushCache();
		
		$p1 = $this->objFromFixture('Page', 'product1');
		$vp1 = $this->objFromFixture('VirtualPage', 'vproduct1');
		
		$this->assertEquals('New Product 1', $p1->Title);
		$this->assertEquals('New Product 1', $vp1->Title);
	}	
	
	function setUp() {
		parent::setUp();
		
		// Publish all but the embargoed content and switch view to Live
		$pages = array('home', 'about', 'staff', 'staffduplicate','products', 'product1', 
			'product2', 'product5', 'contact', 'virtuals');
		foreach($pages as $page) $this->objFromFixture('Page', $page)->doPublish();

		$this->objFromFixture('ErrorPage', 404)->doPublish();
		$this->objFromFixture('VirtualPage', 'vproduct1')->doPublish();
		$this->objFromFixture('VirtualPage', 'vproduct2')->doPublish();
		$this->objFromFixture('VirtualPage', 'vproduct5')->doPublish();
		
		Versioned::reading_stage('Live');
	}

	function tearDown() {
		SiteTreeFutureState::set_future_datetime(null);
		Versioned::reading_stage('Stage');
		
		parent::tearDown();
	}
}
