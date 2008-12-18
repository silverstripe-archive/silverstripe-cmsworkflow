<?php
/**
 * Adds a new "sidereport" in the CMS listing all pages which require publication.
 * 
 * @package cmsworkflow
 */
class MyPublicationRequestsSideReport extends SideReport {
	function title() {
		return _t('PublisherReviewSideReport.TITLE',"Awaiting publication");
	}
	function records() {
		return WorkflowRequest::get_by_publisher(
			'WorkflowPublicationRequest',
			Member::currentUser(),
			array('AwaitingApproval')
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