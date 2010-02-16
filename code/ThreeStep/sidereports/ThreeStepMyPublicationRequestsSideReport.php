<?php
/**
 * Report showing my publication requests
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class ThreeStepMyPublicationRequestsSideReport extends SSReport {
	function title() {
		return _t('ThreeStepMyPublicationRequestsSideReport.TITLE',"My publication requests");
	}
	function group() {
		return "Workflow reports";
	}
	function sort() {
		return -200;
	}
	function sourceRecords($params) {
		return WorkflowThreeStepRequest::get_by_author(
			'WorkflowPublicationRequest',
			Member::currentUser(),
			array('AwaitingApproval', 'Approved')
		);
	}
	function columns() {
		return array(
			"Title" => array(
				"title" => "Title",
				"link" => true,
			),
		);
	}
	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	}
}

?>