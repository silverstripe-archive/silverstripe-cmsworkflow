<?php
/**
 * Report showing removal requests I need to publish
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class ThreeStepWorkflowRemovalRequestsNeedingPublishingSideReport extends SideReport {
	function title() {
		return _t('ThreeStepWorkflowRemovalRequestsNeedingPublishingSideReport.TITLE',"Removal requests I need to publish");
	}
	function group() {
		return "Workflow reports";
	}
	function sort() {
		return 400;
	}
	function records() {
		if (ClassInfo::exists('Subsite') && isset($this->params['AllSubsites'])) {
			$oldSSFilterState = Subsite::$disable_subsite_filter;
			Subsite::$disable_subsite_filter = true;
		}
		
		$res = WorkflowThreeStepRequest::get_by_publisher(
			'WorkflowDeletionRequest',
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

?>