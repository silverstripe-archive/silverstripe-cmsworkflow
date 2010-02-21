<?php
/**
 * Report showing removal requests I need to approve
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class UnapprovedDeletions3StepReport extends SS_Report {
	function title() {
		return _t('UnapprovedDeletions3StepReport.TITLE',"Deletion requests I need to approve");
	}
	function sourceRecords($params, $sort, $limit) {
		$res = WorkflowThreeStepRequest::get_by_approver(
			'WorkflowDeletionRequest',
			Member::currentUser(),
			array('AwaitingApproval')
		);

		
		SiteTree::prepopuplate_permission_cache('CanApproveType', $res->column('ID'), 
			"SiteTreeCMSThreeStepWorkflow::can_approve_multiple");
		SiteTree::prepopuplate_permission_cache('CanEditType', $res->column('ID'),
			"SiteTree::can_edit_multiple");

		$doSet = new DataObjectSet();
		if ($res) {
			foreach ($res as $result) {
				if (!$result->canApprove()) continue;
				if ($wf = $result->openWorkflowRequest()) {
					$result->WFAuthorTitle = $wf->Author()->Title;
					$result->WFAuthorID = $wf->AuthorID;
					$result->WFRequestedWhen = $wf->Created;
					$result->WFApproverID = $wf->ApproverID;
					$result->WFPublisherID = $wf->PublisherID;
					$result->BacklinkCount = $result->BackLinkTracking()->Count();
					$doSet->push($result);
				}
			}
		}
		
		if($sort) {
			$parts = explode(' ', $sort);
			$field = $parts[0];
			$direction = $parts[1];
			
			if($field == 'AbsoluteLink') $sort = 'URLSegment ' . $direction;
			if($field == 'Subsite.Title') $sort = 'SubsiteID ' . $direction;
			
			$doSet->sort($sort);
		}
		
		if($limit && $limit['limit']) return $doSet->getRange($limit['start'], $limit['limit']);
		else return $doSet;
	}
	function columns() {
		return array(
			"Title" => array(
				"title" => "Page name",
				'formatting' => '<a href=\"admin/show/$ID\" title=\"Edit page\">$value</a>'
			),
			"WFAuthorTitle" => array(
				"title" => "Requested by",
			),
			"WFRequestedWhen" => array(
				"title" => "Requested",
				'casting' => 'SS_Datetime->Full',
			),
			'AbsoluteLink' => array(
				'title' => 'URL',
				'formatting' => '$value " . ($AbsoluteLiveLink ? "<a target=\"_blank\" href=\"$AbsoluteLiveLink\">(live)</a>" : "") . " <a target=\"_blank\" href=\"$value?stage=Stage\">(draft)</a>'
			),
			"BacklinkCount" => array(
				"title" => "Incoming links",
				'formatting' => '".($value ? "<a href=\"admin/show/$ID#Root_Expiry\" title=\"View backlinks\">yes, $value</a>" : "none") . "'
			),
		);
	}

	
	/**
	 * This alternative columns method is picked up by SideReportWrapper
	 */
	function sideReportColumns() {
		return array(
			"Title" => array(
				"link" => true,
			),
			"WFAuthorTitle" => array(
				"formatting" => 'Requested by $value',
			),
			"WFRequestedWhen" => array(
				"formatting" => ' on $value',
				'casting' => 'SS_Datetime->Full'
			),
		);
	}
	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	}
	
	function group() {
		return _t('WorkflowRequest.WORKFLOW', 'Workflow');
	}
}
