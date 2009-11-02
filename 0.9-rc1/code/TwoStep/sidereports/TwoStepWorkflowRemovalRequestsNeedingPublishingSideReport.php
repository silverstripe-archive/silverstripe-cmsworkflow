<?php
/**
 * Report showing removal requests I need to publish
 * 
 * @package cmsworkflow
 * @subpackage TwoStep
 */
class TwoStepWorkflowRemovalRequestsNeedingPublishingSideReport extends SideReport {
	function title() {
		return _t('TwoStepWorkflowRemovalRequestsNeedingPublishingSideReport.TITLE',"Removal requests I need to publish");
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
		
		$res = WorkflowRequest::get_by_approver(
			'WorkflowRemovalRequest',
			Member::currentUser(),
			array('AwaitingApproval')
		);
		
		if (!count($res)) return false;
		
		$doSet = new DataObjectSet();
		foreach ($res as $result) {
			if ($wf = $result->openWorkflowRequest()) {
				if (!$result->canApprove()) continue;
				$result->WFRequesterEmail = $wf->Author()->Email;
				$result->WFRequestedWhen = $wf->Created;
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
				"prefix" => 'Requested by ',
				"source" => "WFRequesterEmail",
				"link" => false,
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
		return Object::has_extension('SiteTree', 'SiteTreeCMSTwoStepWorkflow');
	}
}

?>