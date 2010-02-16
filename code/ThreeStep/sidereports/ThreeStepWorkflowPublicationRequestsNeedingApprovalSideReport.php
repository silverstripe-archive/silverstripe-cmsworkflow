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
		if (ClassInfo::exists('Subsite') && isset($params['AllSubsites'])) {
			$oldSSFilterState = Subsite::$disable_subsite_filter;
			Subsite::$disable_subsite_filter = true;
		}
		
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
				"title" => "Requester",
				"formatting" => 'Requested by $value',
			),
			"WFRequestedWhen" => array(
				"title" => "When",
				"formatting" => ' on $value',
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