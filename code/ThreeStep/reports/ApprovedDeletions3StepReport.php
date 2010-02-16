<?php
/**
 * Report showing removal requests I need to publish
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class ApprovedDeletions3StepReport extends SSReport {
	function title() {
		return _t('ApprovedDeletions3StepReport.TITLE',"Approved deletions I need to publish");
	}
	function sourceRecords($params) {
		$res = WorkflowThreeStepRequest::get_by_publisher(
			'WorkflowDeletionRequest',
			Member::currentUser(),
			array('Approved')
		);
		$doSet = new DataObjectSet();
		foreach ($res as $result) {
			if ($wf = $result->openWorkflowRequest()) {
				if (!$result->canDeleteFromLive()) continue;
				$result->WFAuthorID = $wf->AuthorID;
				$result->WFApproverEmail = $wf->Approver()->Email;
				$result->WFRequesterEmail = $wf->Author()->Email;
				$result->WFApprovedWhen = $wf->ApprovalDate();
				$result->WFRequestedWhen = $wf->Created;
				$result->WFApproverID = $wf->ApproverID;
				$result->WFPublisherID = $wf->PublisherID;
				if (isset($_REQUEST['OnlyMine']) && $result->WFApproverID != Member::currentUserID()) continue;
				$doSet->push($result);
			}
		}
		
		return $doSet;
	}
	function columns() {
		return array(
			"Title" => array(
				"title" => "Title",
				'formatting' => '<a href=\"admin/show/$ID\" title=\"Edit page\">$value</a>'
			),
			"WFApproverEmail" => array(
				"title" => "Approver",
				"link" => false,
			),
			"WFApprovedWhen" => array(
				"title" => "Approved",
				"link" => false,
				'casting' => 'SSDatetime->Full'
			),
			"WFRequesterEmail" => array(
				"title" => "Author",
				"link" => false,
			),
			"WFRequestedWhen" => array(
				"title" => "Requested",
				"link" => false,
				'casting' => 'SSDatetime->Full'
			),
			'AbsoluteLink' => array(
				'title' => 'URL',
				'formatting' => '$value " . ($AbsoluteLiveLink ? "<a href=\"$AbsoluteLiveLink\">(live)</a>" : "") . " <a href=\"$value?stage=Stage\">(draft)</a>'
			)
		);
	}
	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	}
	function parameterFields() {
		$params = new FieldSet();
		
		$params->push(new CheckboxField(
			"OnlyMine", 
			"Only requests I approved" 
		));
		
		return $params;
	}
}

?>
