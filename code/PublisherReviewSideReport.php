<?php

class PublisherReviewSideReport extends SideReport {
	function title() {
		return _t('PublisherReviewSideReport.TITLE',"Needing to be published");
	}
	function records() {
		return DataObject::get("SiteTree", "`SiteTree`.NeedsPublication", "`SiteTree`.`LastEdited` DESC");
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