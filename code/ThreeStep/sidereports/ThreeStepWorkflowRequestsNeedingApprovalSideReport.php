<?php
/**
 * Report showing my publication requests
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class ThreeStepWorkflowRequestsNeedingApprovalSideReport_ThisSubsite extends SideReport {
	function title() {
		return _t('ThreeStepWorkflowRequestsNeedingApprovalSideReport.TITLE',"Workflow: requests I need to approve");
	}
	function records() {
		$res = WorkflowThreeStepRequest::get_by_approver(
			'WorkflowRequest',
			Member::currentUser(),
			array('AwaitingApproval')
		);
		$doSet = new DataObjectSet();
		if ($res) {
			foreach ($res as $result) {
				if ($wf = $result->openWorkflowRequest()) {
					if (!$result->canPublish()) continue;
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
	function fieldsToShow() {
		return array(
			"Title" => array(
				"source" => array("NestedTitle", array("2")),
				"link" => true,
			),
			"Requester" => array(
				"prefix" => 'Approval requested by ',
				"source" => "WFAuthorEmail",
			)
		);
	}
}

class ThreeStepWorkflowRequestsNeedingApprovalSideReport_AllSubsites extends SideReport {
	function title() {
		return _t('MyWorkflowRequestsSideReport.TITLE',"Workflow: requests I need to approve (all subsites)");
	}
	function records() {
		if (ClassInfo::exists('Subsite')) Subsite::$disable_subsite_filter = true;
		$res = WorkflowThreeStepRequest::get_by_approver(
			'WorkflowRequest',
			Member::currentUser(),
			array('AwaitingApproval')
		);
		$doSet = new DataObjectSet();
		if ($res) {
			foreach ($res as $result) {
				if ($wf = $result->openWorkflowRequest()) {
					if (!$result->canPublish()) continue;
					$result->WFAuthorID = $wf->AuthorID;
					$result->WFAuthorEmail = $wf->Author()->Email;
					$result->WFApproverID = $wf->ApproverID;
					$result->WFPublisherID = $wf->PublisherID;
					$doSet->push($result);
				}
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
			"Requester" => array(
				"prefix" => 'Approval requested by ',
				"source" => "WFAuthorEmail",
			)
		);
	}
}

