<?php
/**
 * Report showing removal requests I need to approve
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class UnapprovedDeletions3StepReport extends SSReport {
	function title() {
		return _t('UnapprovedDeletions3StepReport.TITLE',"Deletion requests I need to approve");
	}
	function group() {
		return "Workflow reports";
	}
	function sort() {
		return 200;
	}
	function sourceRecords($params) {
		$res = WorkflowThreeStepRequest::get_by_approver(
			'WorkflowDeletionRequest',
			Member::currentUser(),
			array('AwaitingApproval')
		);
		$doSet = new DataObjectSet();
		if ($res) {
			foreach ($res as $result) {
				if ($wf = $result->openWorkflowRequest()) {
					if (!$result->canApprove()) continue;
					$result->WFAuthorID = $wf->AuthorID;
					$result->WFRequesterEmail = $wf->Author()->Email;
					$result->WFRequestedWhen = $wf->Created;
					$result->WFApproverID = $wf->ApproverID;
					$result->WFPublisherID = $wf->PublisherID;
					$doSet->push($result);
				}
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
