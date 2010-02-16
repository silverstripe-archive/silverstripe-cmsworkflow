<?php
/**
 * Report showing removal requests I need to approve
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class ThreeStepWorkflowRemovalRequestsNeedingApprovalSideReport extends SSReport {
	function title() {
		return _t('ThreeStepWorkflowRemovalRequestsNeedingApprovalSideReport.TITLE',"Removal requests I need to approve");
	}
	function group() {
		return "Workflow reports";
	}
	function sort() {
		return 200;
	}
	function sourceRecords($params) {
		if (ClassInfo::exists('Subsite') && isset($params['AllSubsites'])) {
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
		
		if (ClassInfo::exists('Subsite') && isset($params['AllSubsites'])) {
			Subsite::$disable_subsite_filter = $oldSSFilterState;
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
				"formatting" => 'Requested by $value',
				"source" => "Requester",
			),
			"WFRequestedWhen" => array(
				"formatting" => ' on $value',
				"title" => "When",
				"link" => false,
			)
		);
	}
	function parameterFields() {
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
