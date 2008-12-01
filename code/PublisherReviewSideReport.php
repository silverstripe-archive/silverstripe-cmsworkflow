<?php
/**
 * Adds a new "sidereport" in the CMS listing all pages which require publication.
 * 
 * @package cmsworkflow
 */
class PublisherReviewSideReport extends SideReport {
	function title() {
		return _t('PublisherReviewSideReport.TITLE',"Needing to be published");
	}
	function records() {
		return DataObject::get("SiteTree", "`SiteTree`.NeedsReview", "`SiteTree`.`LastEdited` DESC");
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