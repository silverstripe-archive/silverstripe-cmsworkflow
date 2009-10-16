<?php

/**
 * Filter the SiteTree by pages awaiting approval
 *
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class CMSWorkflowThreeStepFilters_PagesAwaitingApproval extends CMSSiteTreeFilter {
	static function title() {
		return _t('CMSWorkflowThreeStepFilters_PagesAwaitingApproval.TITLE', "Pages awaiting approval");
	}
	
	static function showNode($node) {
		if ($wf = $node->openWorkflowRequest()) {
			return $wf->Status == 'AwaitingApproval' ? true : false;
		}
		return false;
	}
	
	function getTree() {
		$leftAndMain = new LeftAndMain();
		$tree = $leftAndMain->getSiteTreeFor('SiteTree', isset($_REQUEST['ID']) ? $_REQUEST['ID'] : 0, null, array(__CLASS__, 'showNode'));
	
		// Trim off the outer tag
		$tree = ereg_replace('^[ \t\r\n]*<ul[^>]*>','', $tree);
		$tree = ereg_replace('</ul[^>]*>[ \t\r\n]*$','', $tree);
	
		return $tree;
	}
}

/**
 * Fitler the SiteTree by pages awaiting publishing
 *
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class CMSWorkflowThreeStepFilters_PagesAwaitingPublishing extends CMSSiteTreeFilter {
	static function title() {
		return _t('CMSWorkflowThreeStepFilters_PagesAwaitingPublishing.TITLE', "Pages awaiting publishing");
	}
	
	static function showNode($node) {
		if ($wf = $node->openWorkflowRequest()) {
			return $wf->Status == 'Approved' ? true : false;
		}
	}
	
	function getTree() {
		$leftAndMain = new LeftAndMain();
		$tree = $leftAndMain->getSiteTreeFor('SiteTree', isset($_REQUEST['ID']) ? $_REQUEST['ID'] : 0, null, array(__CLASS__, 'showNode'));

		// Trim off the outer tag
		$tree = ereg_replace('^[ \t\r\n]*<ul[^>]*>','', $tree);
		$tree = ereg_replace('</ul[^>]*>[ \t\r\n]*$','', $tree);

		return $tree;
	}
}