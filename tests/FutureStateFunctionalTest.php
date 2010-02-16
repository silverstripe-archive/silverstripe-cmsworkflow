<?php

class FutureStateFunctionalTest extends FunctionalTest {
	static $fixture_file = 'cmsworkflow/tests/SiteTreeFutureStateTest.yml';
	
	function testFutureStateAsksForLogIn() {
		$this->autoFollowRedirection = false;
		
		$response = $this->get('/about-us?futureDate=2020-10-10+10:00:00');
		
		$this->assertEquals(302, $response->getStatusCode());
		$this->assertContains('Security/login', $response->getHeader('Location'));
		
	}

	///////////////////////////////////////////////////////////////////////////////////////////
	
	function setUp() {
		parent::setUp();
		
		// Publish all but the embargoed content and switch view to Live
		$pages = array('home', 'about', 'staff', 'staffduplicate','products', 'product1', 
			'product2', 'contact', 'virtuals');
			
		Versioned::reading_stage('Stage');

		$this->logInWithPermssion('ADMIN');
		foreach($pages as $page) {
			$this->assertTrue($this->objFromFixture('Page', $page)->doPublish());
		}
		$this->get('Security/logout');

		Versioned::reading_stage('Live');
	}
}