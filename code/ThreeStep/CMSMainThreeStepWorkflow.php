<?php

class CMSMainThreeStepWorkflow extends Extension {
	public static $allowed_actions = array(
		'getfilteredsubtree_awaiting_approval',
		'getfilteredsubtree_awaiting_publish',
	);
	
	static function filterAwaitingApproval($node) {
		if ($wf = $node->openWorkflowRequest()) {
			return $wf->Status == 'AwaitingApproval' ? true : false;
		}
	}
	static function filterAwaitingPublish($node) {
		if ($wf = $node->openWorkflowRequest()) {
			return $wf->Status == 'Approved' ? true : false;
		}
	}
	
	public function getfilteredsubtree_awaiting_approval() {
		$filter = array(__CLASS__, 'filterAwaitingApproval');
		return $this->filterSitetree($filter);
	}
	
	public function getfilteredsubtree_awaiting_publish() {
		$filter = array(__CLASS__, 'filterAwaitingPublish');
		return $this->filterSitetree($filter);
	}
	
	public function filterSitetree($filter) {
		// Get the tree
		$tree = $this->owner->getSiteTreeFor($this->owner->stat('tree_class'), $_REQUEST['ID'], null, $filter);

		// Trim off the outer tag
		$tree = ereg_replace('^[ \t\r\n]*<ul[^>]*>','', $tree);
		$tree = ereg_replace('</ul[^>]*>[ \t\r\n]*$','', $tree);
	
		return $tree;
	}
}