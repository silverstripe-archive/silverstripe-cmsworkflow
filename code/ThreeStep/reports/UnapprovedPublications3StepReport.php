<?php
/**
 * Report showing publication requests I need to approve
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class UnapprovedPublications3StepReport extends SSReport {
	function title() {
		return _t('UnapprovedPublications3StepReport.TITLE',"Publication requests I need to approve");
	}
	
	function sourceRecords($params, $sort, $limit) {
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
					if(ClassInfo::exists('Subsite')) $result->SubsiteTitle = $result->Subsite()->Title;
					$result->AuthorTitle = $wf->Author()->Title;
					$result->RequestedAt = $wf->Created;
					$result->HasEmbargoOrExpiry = $wf->getEmbargoDate() || $wf->ExpiryDate() ? 'yes' : 'no';
					$doSet->push($result);
				}
			}
		}
		
		if ($sort) $doSet->sort($sort);

		return $doSet;
	}
	
	function columns() {
		$fields = array(
			'Title' => array(
				'title' => 'Title',
				'formatting' => '<a href=\"admin/show/$ID\" title=\"Edit page\">$value</a>'
			),
			'AuthorTitle' => 'Requested by',
			'RequestedAt' => array(
				'title' => 'Requested',
				'casting' => 'SSDatetime->Full'
			),
			'HasEmbargoOrExpiry' => 'Scheduled',
			'AbsoluteLink' => array(
				'title' => 'Links',
				'formatting' => '$value " . ($AbsoluteLiveLink ? "<a href=\"$AbsoluteLiveLink\">(live)</a>" : "") . " <a href=\"$value?stage=Stage\">(draft)</a>'
			)
		);
		
		return $fields;
	}

	/**
	 * This alternative columns method is picked up by SideReportWrapper
	 */
	function sideReportColumns() {
		return array(
			'Title' => array(
				'link' => 'true',
			),
			'AuthorTitle' => array(
				'formatting' => 'Requested by $value'
			),
			'RequestedAt' => array(
				'formatting' => ' on $value',
				'casting' => 'SSDatetime->Full'
			),
		);
	}
	
	function sortColumns() {
		return array(
			'SubsiteTitle',
			'AuthorTitle',
			'RequestedAt'
		);
	}
	
	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	}
}
