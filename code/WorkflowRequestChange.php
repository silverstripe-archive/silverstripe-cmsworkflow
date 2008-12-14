<?php
/**
 * Tracks the history of a {@link WorkflowRequest} object.
 * A new object is created whenever the Status property of a request
 * changes, including the author and the current page version of the draft site.
 * 
 * @package cmsworkflow
 */
class WorkflowRequestChange extends DataObject {
	static $db = array(
		'Status' => 'Varchar', // @see WorkflowRequest->Status
		'PageDraftVersion' => 'Int', // version of the page at draft stage
		'PageLiveVersion' => 'Int', // version of the page at live/published stage
	);
	
	static $has_one = array(
		'Author' => 'Member', 
		'WorkflowRequest' => 'WorkflowRequest', 
	);
}
?>