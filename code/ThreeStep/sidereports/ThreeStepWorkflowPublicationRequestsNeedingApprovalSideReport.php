<?php
/**
 * Report showing publication requests I need to approve
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class ThreeStepWorkflowPublicationRequestsNeedingApprovalSideReport extends SSReport {
	function title() {
		return _t('ThreeStepWorkflowPublicationRequestsNeedingApprovalSideReport.TITLE',"Publication requests I need to approve");
	}
	function group() {
		return "Workflow reports";
	}
	function sort() {
		return 100;
	}
	function sourceRecords($params) {
		$res = WorkflowThreeStepRequest::get_by_approver(
			'WorkflowPublicationRequest',
			Member::currentUser(),
			array('AwaitingApproval')
		);
		$doSet = new DataObjectSet();
		if ($res) {
			foreach ($res as $result) {
				if ($wf = $result->openWorkflowRequest()) {
					if (!$result->canApprove()) continue;
					$result->WFRequestedWhen = $wf->Created;
					$result->WFAuthorID = $wf->AuthorID;
					$result->WFAuthorEmail = $wf->Author()->Email;
					$result->WFApproverID = $wf->ApproverID;
					$result->WFPublisherID = $wf->PublisherID;
					$doSet->push($result);
				}
			}
		}
		
		return $doSet;
	}
	function columns() {
		return array(
			"Title" => array(
				"title" => "Title",
				"link" => true,
			),
			"WFAuthorEmail" => array(
				"title" => "Requester",
				"formatting" => 'Requested by $value',
			),
			"WFRequestedWhen" => array(
				"title" => "When",
				"formatting" => ' on $value',
			)
		);
	}
	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	}
}