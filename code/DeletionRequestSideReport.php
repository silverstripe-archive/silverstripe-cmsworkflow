<?php
/**
 * Adds a new "sidereport" in the CMS listing all pages which require publication.
 * 
 * @package cmsworkflow
 */
class DeletionRequestSideReport extends SideReport {
	function title() {
		return _t('DeletionRequestSideReport.TITLE',"Awaiting deletion");
	}
	function records() {
		return WorkflowRequest::get_by_publisher(
			'WorkflowDeletionRequest',
			Member::currentUser()
		);
	}
	function fieldsToShow() {
		return array(
			"Title" => array(
				"source" => array("NestedTitle", array("2")),
				"link" => true,
			),
		);
	}
}

?>