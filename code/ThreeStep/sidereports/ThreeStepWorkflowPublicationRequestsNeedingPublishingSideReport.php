<?php
/**
 * Report showing publication requests I need to publish
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class ApprovedPublications3StepReport extends SSReport {
	function title() {
		return _t('ApprovedPublications3StepReport.TITLE',"Approved pages I need to publish");
	}
	function group() {
		return "Workflow reports";
	}
	function sourceRecords($params) {
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
				$result->WFRequesterEmail = $wf->Author()->Email;
				$result->WFApprovedWhen = $wf->ApprovalDate();
				$result->WFRequestedWhen = $wf->Created;
				$result->WFApproverID = $wf->ApproverID;
				$result->WFPublisherID = $wf->PublisherID;
				$result->HasEmbargoOrExpiry = $wf->getEmbargoDate() || $wf->ExpiryDate() ? 'yes' : 'no';
				if (isset($_REQUEST['OnlyMine']) && $result->WFApproverID != Member::currentUserID()) continue;
				$doSet->push($result);
			}
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
				"title" => "Approver",
				"formatting" => 'Approved by $value',
				"link" => false,
			),
			"WFApprovedWhen" => array(
				"title" => "When",
				"formatting" => ' on $value',
				"link" => false,
			),
			"WFRequesterEmail" => array(
				"title" => "Author",
				"formatting" => 'Requested by $value',
				"link" => false,
			),
			"WFRequestedWhen" => array(
				"title" => "When",
				"formatting" => ' on $value',
				"link" => false,
			),
			'HasEmbargoOrExpiry' => 'Embargo or expiry dates set'
		);
	}
	
	function parameterFields() {
		$fieldset = new FieldSet(
			new CheckboxField('OnlyMine', 'Only requests I approved')
		);
		return $fieldset;
	}

	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	}
}
?>