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
	function group() {
		return "Workflow reports";
	}
	function sort() {
		return 400;
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
			'AbsoluteLink' => array(
				'title' => 'URL',
				'formatting' => '<a href=\"admin/show/$ID\" title=\"Edit page\">$value</a> " . ($AbsoluteLiveLink ? "<a href=\"$AbsoluteLiveLink\">(live)</a>" : "") . " <a href=\"$value?stage=Stage\">(draft)</a>',
			)
		);
	}

	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	}
}

?>