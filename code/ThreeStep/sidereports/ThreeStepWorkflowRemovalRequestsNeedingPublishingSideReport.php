<?php
/**
 * Report showing removal requests I need to publish
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class ThreeStepWorkflowRemovalRequestsNeedingPublishingSideReport_ThisSubsite extends SideReport {
	function title() {
		return _t('ThreeStepWorkflowRemovalRequestsNeedingPublishingSideReport.TITLE',"Workflow: removal requests I need to publish");
	}
	function records() {
		$res = WorkflowThreeStepRequest::get_by_publisher(
			'WorkflowRemovalRequest',
			Member::currentUser(),
			array('Approved')
		);
		$doSet = new DataObjectSet();
		foreach ($res as $result) {
			if ($wf = $result->openWorkflowRequest()) {
				if (!$result->canApprove()) continue;
				$result->WFAuthorID = $wf->AuthorID;
				$result->WFApproverEmail = $wf->Approver()->Email;
				$result->WFApprovedWhen = $wf->ApprovalDate();
				$result->WFApproverID = $wf->ApproverID;
				$result->WFPublisherID = $wf->PublisherID;
				if (isset($_REQUEST['OnlyMine']) && $result->WFApproverID != Member::currentUserID()) continue;
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
				"prefix" => 'Approved by ',
				"source" => "WFApproverEmail",
				"link" => false,
			),
			"When" => array(
				"prefix" => ' on ',
				"source" => "WFApprovedWhen",
				"link" => false,
			)
		);
	}
	function getParameterFields() {
		return new FieldSet(
			new CheckboxField('OnlyMine', 'Only requests I approved')
		);
	}
}

class ThreeStepWorkflowRemovalRequestsNeedingPublishingSideReport_AllSubsites extends SideReport {
	function title() {
		return _t('MyWorkflowRemovalRequestsSideReport.ALLSUBSITES',"Workflow: removal requests I need to publish (all subsites)");
	}
	function records() {
		if (ClassInfo::exists('Subsite')) Subsite::$disable_subsite_filter = true;
		$res = WorkflowThreeStepRequest::get_by_publisher(
			'WorkflowRemovalRequest',
			Member::currentUser(),
			array('Approved')
		);
		$doSet = new DataObjectSet();
		foreach ($res as $result) {
			if ($wf = $result->openWorkflowRequest()) {
				if (!$result->canApprove()) continue;
				$result->WFAuthorID = $wf->AuthorID;
				$result->WFApproverEmail = $wf->Approver()->Email;
				$result->WFApprovedWhen = $wf->ApprovalDate();
				$result->WFApproverID = $wf->ApproverID;
				$result->WFPublisherID = $wf->PublisherID;
				if (isset($_REQUEST['OnlyMine']) && $result->WFApproverID != Member::currentUserID()) continue;
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
				"reload" => true
			),
			"Author" => array(
				"prefix" => 'Approved by ',
				"source" => "WFApproverEmail",
				"link" => false,
			),
			"When" => array(
				"prefix" => ' on ',
				"source" => "WFApprovedWhen",
				"link" => false,
			)
		);
	}
	function getParameterFields() {
		return new FieldSet(
			new CheckboxField('OnlyMine', 'Only requests I approved')
		);
	}
}

?>