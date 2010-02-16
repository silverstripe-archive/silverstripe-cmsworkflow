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
			'Title' => 'Title',
			'AuthorTitle' => 'Requested by',
			'RequestedAt' => array(
				'title' => 'Requested at',
				'casting' => 'SSDatetime->Full'
			),
			'HasEmbargoOrExpiry' => 'Embargo or expiry dates set',
			'AbsoluteLink' => array(
				'title' => 'Links',
				'formatting' => '<a href=\"admin/show/$ID\" title=\"Edit page\">$value</a> " . ($AbsoluteLiveLink ? "<a href=\"$AbsoluteLiveLink\">(live)</a>" : "") . " <a href=\"$value?stage=Stage\">(draft)</a>'
			)
		);
		
		return $fields;
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
