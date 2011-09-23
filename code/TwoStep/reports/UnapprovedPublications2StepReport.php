<?php
/**
 * Report showing publication requests I need to approve
 * 
 * @package cmsworkflow
 * @subpackage TwoStep
 */
class UnapprovedPublications2StepReport extends SS_Report {
	function title() {
		return _t('UnapprovedPublications2StepReport.TITLE',"Publication requests I need to approve");
	}
	
	function sourceRecords($params, $sort, $limit) {
		increase_time_limit_to(120);
		
		$res = WorkflowTwoStepRequest::get_by_publisher(
			'WorkflowPublicationRequest',
			Member::currentUser(),
			array('AwaitingApproval')
		);
		
		$doSet = new DataObjectSet();
		if ($res) {
			foreach ($res as $result) {
				if (!$result->canPublish()) continue;
				if ($wf = $result->openWorkflowRequest()) {
					if(ClassInfo::exists('Subsite')) $result->SubsiteTitle = $result->Subsite()->Title;
					$result->RequestedAt = $wf->Created;
					$result->WFAuthorTitle = $wf->Author()->Title;
					$result->HasEmbargo = $wf->getEmbargoDate() ? date('j M Y g:ia', strtotime($wf->getEmbargoDate())) : 'no';
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
		$fields = array(
			'Title' => array(
				'title' => 'Page name',
				'formatting' => '<a href=\"admin/show/$ID\" title=\"Edit page\">$value</a>'
			),
			'WFAuthorTitle' => 'Requested by',
			'RequestedAt' => array(
				'title' => 'Requested',
				'casting' => 'SS_Datetime->Full'
			),
			'HasEmbargo' => 'Embargo',
			'AbsoluteLink' => array(
				'title' => 'URL',
				'formatting' => '$value " . ($AbsoluteLiveLink ? "<a target=\"_blank\" href=\"$AbsoluteLiveLink\">(live)</a>" : "") . " <a target=\"_blank\" href=\"$value?stage=Stage\">(draft)</a>'
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
				'link' => true,
			),
			'WFAuthorTitle' => array(
				'formatting' => 'Requested by $value'
			),
			'RequestedAt' => array(
				'formatting' => ' on $value',
				'casting' => 'SS_Datetime->Full'
			),
		);
	}
	
	function sortColumns() {
		return array(
			'SubsiteTitle',
			'WFAuthorTitle',
			'RequestedAt'
		);
	}
	
	function group() {
		return _t('WorkflowRequest.WORKFLOW', 'Workflow');
	}

	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSTwoStepWorkflow');
	}
}
