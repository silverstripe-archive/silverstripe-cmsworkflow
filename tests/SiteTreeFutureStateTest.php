<?php

/**
 * Tests the functionality for previewing the future state of the site.
 */
class SiteTreeFutureStateTest extends SapphireTest {
	static $fixture_file = 'cmsworkflow/tests/SiteTreeFutureStateTest.yml';

	function testTopLevelPagesArentAffectedByEmbargoedChildren() {
		// The top-level items have no embargo/expiry, and so should be unaffected by the embargoes
		// of their children
		
		$items1 = DataObject::get("SiteTree", "ParentID = 0")->column("Title");
		SiteTreeFutureState::set_future_datetime('2020-01-01 10:00:00');
		$items2 = DataObject::get("SiteTree", "ParentID = 0")->column("Title");
		SiteTreeFutureState::set_future_datetime('2020-01-01 10:59:00');
		$items3 = DataObject::get("SiteTree", "ParentID = 0")->column("Title");
		SiteTreeFutureState::set_future_datetime('2020-01-01 11:01:00');
		$items4 = DataObject::get("SiteTree", "ParentID = 0")->column("Title");
		SiteTreeFutureState::set_future_datetime('2020-01-03 11:01:00');
		$items5 = DataObject::get("SiteTree", "ParentID = 0")->column("Title");
		
		$this->assertEquals(array('Home', 'About Us', 'Products', 'Contact Us'), $items1);
		$this->assertEquals(array('Home', 'About Us', 'Products', 'Contact Us'), $items2);
		$this->assertEquals(array('Home', 'About Us', 'Products', 'Contact Us'), $items3);
		$this->assertEquals(array('Home', 'About Us', 'Products', 'Contact Us'), $items4);
		$this->assertEquals(array('Home', 'About Us', 'Products', 'Contact Us'), $items5);
	}


	function testEmbargoAndExpiryAffectsRegularDataObjectRequets() {
		$products = $this->objFromFixture('Page', 'products');
		
		// The top-level items have no embargo/expiry, and so should be unaffected by the embargoes
		// of their children
		
		$this->assertEquals(array('Product 1', 'Product 2'),
			$products->Children()->column("Title"));

		// Hasn't changed 1 minute before
		SiteTreeFutureState::set_future_datetime('2020-01-01 09:59:00');
		$this->assertEquals(array('Product 1', 'Product 2'),
			$products->Children()->column("Title"));

		// Product 4 appears on exactly its embargo date
		SiteTreeFutureState::set_future_datetime('2020-01-01 10:00:00');
		$products->flushCache();
		$this->assertEquals(array('Product 1', 'Product 2', 'Product 4'),
			$products->Children()->column("Title"));

		// Product 2 disappears on exactly its expiry date
		SiteTreeFutureState::set_future_datetime('2020-01-01 10:59:00');
		$products->flushCache();
		$this->assertEquals(array('Product 1', 'Product 2', 'Product 4'),
			$products->Children()->column("Title"));
		SiteTreeFutureState::set_future_datetime('2020-01-01 11:00:00');
		$products->flushCache();
		$this->assertEquals(array('Product 1', 'Product 4'),
			$products->Children()->column("Title"));

		SiteTreeFutureState::set_future_datetime('2020-01-03 11:01:00');
		$products->flushCache();
		$this->assertEquals(array('Product 1', 'Product 3', 'Product 4'),
			$products->Children()->column("Title"));
	}
	
	function setUp() {
		parent::setUp();
		
		// Publish all but the embargoed content and switch view to Live
		$pages = array('home', 'about', 'staff', 'staffduplicate','products', 'product1', 
			'product2', 'contact');
		foreach($pages as $page) $this->objFromFixture('Page', $page)->doPublish();

		Versioned::reading_stage('Live');
	}

	function tearDown() {
		SiteTreeFutureState::set_future_datetime(null);
		Versioned::reading_stage('Stage');
		
		parent::tearDown();
	}
}