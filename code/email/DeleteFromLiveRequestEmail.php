<?php
/**
 * Email being sent to all members of the "publication" groups
 * assigned to a specific page, asking them to review and publish
 * changes on this page. Gets triggered whenever an author clicks
 * the "Request Publication" button within the CMS.
 * 
 * @package cmsworkflow
 */
class DeleteFromLiveRequestEmail extends Email { 
	protected $ss_template = "DeleteFromLiveRequestEmail"; 
}

?>