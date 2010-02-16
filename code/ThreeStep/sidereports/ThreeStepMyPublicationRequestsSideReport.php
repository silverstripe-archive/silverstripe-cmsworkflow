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
		$res = WorkflowThreeStepRequest::get_by_author(
			'WorkflowPublicationRequest',
			Member::currentUser(),
			array('AwaitingApproval', 'Approved')
		);
		
		// Add WFRequestedWhen column
		$doSet = new DataObjectSet();
		if ($res) {
			foreach ($res as $result) {
				if ($wf = $result->openWorkflowRequest()) {
					$result->WFRequestedWhen = $wf->Created;
					$doSet->push($result);
				}
			}
		}
	
		return $doSet;
	}
	function columns() {
		return array(
			"Title" => array(
				"link" => true,
			),
			"WFRequestedWhen" => array(
				"formatting" => 'Requested on $value',
				'casting' => 'SSDatetime->Full'
			),
		);
	}
	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	}
}

?>