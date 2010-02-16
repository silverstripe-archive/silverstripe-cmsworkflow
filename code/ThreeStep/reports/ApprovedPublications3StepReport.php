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
				$result->WFApproverTitle = $wf->Approver()->Title;
				$result->WFAuthorTitle = $wf->Author()->Title;
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
	function columns() {
		return array(
			"Title" => array(
				"title" => "Title",
				'formatting' => '<a href=\"admin/show/$ID\" title=\"Edit page\">$value</a>'
			),
			"WFApproverTitle" => array(
				"title" => "Approver",
			),
			"WFApprovedWhen" => array(
				"title" => "Approved",
				'casting' => 'SSDatetime->Full'
			),
			"WFAuthorTitle" => array(
				"title" => "Author",
			),
			"WFRequestedWhen" => array(
				"title" => "Requested",
				'casting' => 'SSDatetime->Full'
			),
			'HasEmbargoOrExpiry' => 'Scheduled',
			'AbsoluteLink' => array(
				'title' => 'URL',
				'formatting' => '$value " . ($AbsoluteLiveLink ? "<a href=\"$AbsoluteLiveLink\">(live)</a>" : "") . " <a href=\"$value?stage=Stage\">(draft)</a>'
			)
		);
	}
	
	/**
	 * This alternative columns method is picked up by SideReportWrapper
	 */
	function sideReportColumns() {
		return array(
			"Title" => array(
				"title" => "Title",
				"link" => true,
			),
			"WFApproverTitle" => array(
				"title" => "Approver",
				"formatting" => 'Approved by $value',
			),
			"WFApprovedWhen" => array(
				"title" => "When",
				"formatting" => ' on $value',
				'casting' => 'SSDatetime->Full'
			),
		);
	}
	
	function parameterFields() {
		$params = new FieldSet();
		
		$params->push(new CheckboxField(
			"OnlyMine", 
			"Only requests I approved" 
		));
		
		return $params;
	}
	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	}
}
?>
