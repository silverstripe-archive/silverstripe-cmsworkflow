<?php
/**
 * Report showing removal requests I need to approve
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class ThreeStepWorkflowRemovalRequestsNeedingApprovalSideReport extends SideReport {
	function title() {
		return _t('ThreeStepWorkflowRemovalRequestsNeedingApprovalSideReport.TITLE',"Removal requests I need to approve");
	}
	function group() {
		return "Workflow reports";
	}
	function sort() {
		return 200;
	}
	function records() {
		if (ClassInfo::exists('Subsite') && isset($this->params['AllSubsites'])) {
			$oldSSFilterState = Subsite::$disable_subsite_filter;
			Subsite::$disable_subsite_filter = true;
		}
		
		$res = WorkflowThreeStepRequest::get_by_approver(
			'WorkflowRemovalRequest',
			Member::currentUser(),
			array('AwaitingApproval')
		);
		$doSet = new DataObjectSet();
		if ($res) {
			foreach ($res as $result) {
				if ($wf = $result->openWorkflowRequest()) {
					if (!$result->canPublish()) continue;
					$result->WFRequestedWhen = $wf->Created;
					$result->WFAuthorID = $wf->AuthorID;
					$result->WFAuthorEmail = $wf->Author()->Email;
					$result->WFApproverID = $wf->ApproverID;
					$result->WFPublisherID = $wf->PublisherID;
					$doSet->push($result);
				}
			}
		}
		
		if (ClassInfo::exists('Subsite') && isset($this->params['AllSubsites'])) {
			Subsite::$disable_subsite_filter = $oldSSFilterState;
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
				"prefix" => 'Requested by ',
				"source" => "WFAuthorEmail",
			),
			"When" => array(
				"prefix" => ' on ',
				"source" => "WFRequestedWhen",
				"link" => false,
			)
		);
	}
	function getParameterFields() {
		if (ClassInfo::exists('Subsite')) {
			return new FieldSet(
				new CheckboxField('AllSubsites', 'All subsites')
			);
		}
	}
	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	}
}
