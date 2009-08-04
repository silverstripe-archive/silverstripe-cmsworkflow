<?php
/**
 * @package cmsworkflow
 */
class ThreeStepWorkflowRequestsNeedingPublishingSideReport_ThisSubsite extends SideReport {
	function title() {
		return _t('MyWorkflowRequestsSideReport.TITLE',"Workflow: requests I need to publish");
	}
	function records() {
		$res = WorkflowThreeStepRequest::get_by_publisher(
			'WorkflowRequest',
			Member::currentUser(),
			array('Approved')
		);
		$doSet = new DataObjectSet();
		foreach ($res as $result) {
			if ($wf = $result->openWorkflowRequest()) {
				if (!$result->canApprove()) continue;
				$result->WFAuthorID = $wf->AuthorID;
				$result->WFApproverEmail = $wf->Approver()->Email;
				$result->WFApproverID = $wf->ApproverID;
				$result->WFPublisherID = $wf->PublisherID;
				$doSet->push($result);
			}
		}
		return $doSet;
	}
	function fieldsToShow() {
		return array(
			"Title" => array(
				"source" => array("NestedTitle", array("2")),
				"link" => true,
			),
			"Author" => array(
				"prefix" => 'Request approved by ',
				"source" => "WFApproverEmail",
				"link" => false,
			)
		);
	}
}

class ThreeStepWorkflowRequestsNeedingPublishingSideReport_AllSubsites extends SideReport {
	function title() {
		return _t('MyWorkflowRequestsSideReport.TITLE',"Workflow: requests I need to publish (all subsites)");
	}
	function records() {
		if (ClassInfo::exists('Subsite')) Subsite::$disable_subsite_filter = true;
		$res = WorkflowThreeStepRequest::get_by_publisher(
			'WorkflowRequest',
			Member::currentUser(),
			array('Approved')
		);
		$doSet = new DataObjectSet();
		foreach ($res as $result) {
			if ($wf = $result->openWorkflowRequest()) {
				if (!$result->canApprove()) continue;
				$result->WFAuthorID = $wf->AuthorID;
				$result->WFApproverEmail = $wf->Approver()->Email;
				$result->WFApproverID = $wf->ApproverID;
				$result->WFPublisherID = $wf->PublisherID;
				$doSet->push($result);
			}
		}
		if (ClassInfo::exists('Subsite')) Subsite::$disable_subsite_filter = false;
		return $doSet;
	}
	function fieldsToShow() {
		return array(
			"Title" => array(
				"source" => array("NestedTitle", array("2")),
				"link" => true,
			),
			"Author" => array(
				"prefix" => 'Request approved by ',
				"source" => "WFApproverEmail",
				"link" => false,
			)
		);
	}
}

?>