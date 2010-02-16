<?php
/**
 * Report showing publication requests I need to publish
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class ThreeStepWorkflowPublicationRequestsNeedingPublishingSideReport extends SSReport {
	function title() {
		return _t('ThreeStepWorkflowPublicationRequestsNeedingPublishingSideReport.TITLE',"Publication requests I need to publish");
	}
	function group() {
		return "Workflow reports";
	}
	function sourceRecords($params) {
		if (ClassInfo::exists('Subsite') && isset($params['AllSubsites'])) {
			$oldSSFilterState = Subsite::$disable_subsite_filter;
			Subsite::$disable_subsite_filter = true;
		}
		
		$res = WorkflowThreeStepRequest::get_by_publisher(
			'WorkflowPublicationRequest',
			Member::currentUser(),
			array('Approved')
		);
		$doSet = new DataObjectSet();
		foreach ($res as $result) {
			if ($wf = $result->openWorkflowRequest()) {
				if (!$result->canPublish()) continue;
				$result->WFAuthorID = $wf->AuthorID;
				$result->WFApproverEmail = $wf->Approver()->Email;
				$result->WFApprovedWhen = $wf->ApprovalDate();
				$result->WFApproverID = $wf->ApproverID;
				$result->WFPublisherID = $wf->PublisherID;
				if (isset($_REQUEST['OnlyMine']) && $result->WFApproverID != Member::currentUserID()) continue;
				$doSet->push($result);
			}
		}
		
		if (ClassInfo::exists('Subsite') && isset($params['AllSubsites'])) {
			Subsite::$disable_subsite_filter = $oldSSFilterState;
		}
		
		return $doSet;
	}

	function sort() {
		return 300;
	}

	function columns() {
		return array(
			"Title" => array(
				"title" => "Title",
				"link" => true,
			),
			"WFApproverEmail" => array(
				"title" => "Author",
				"formatting" => 'Approved by $value',
				"link" => false,
			),
			"WFApprovedWhen" => array(
				"title" => "When",
				"formatting" => ' on $value',
				"link" => false,
			)
		);
	}
	
	function getParameterFields() {
		$fieldset = new FieldSet(
			new CheckboxField('OnlyMine', 'Only requests I approved')
		);
		if (ClassInfo::exists('Subsite')) {
			$fieldset->push(new CheckboxField('AllSubsites', 'All subsites'));
		}
		return $fieldset;
	}

	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	}
}
?>