<?php
/**
 * Report showing publication requests I need to approve
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class ThreeStepWorkflowPublicationRequestsNeedingApprovalReport extends SSReport {
	function title() {
		return _t('ThreeStepWorkflowPublicationRequestsNeedingApprovalReport.TITLE',"Workflow: publication requests I need to approve");
	}
	
	function sourceRecords($params) {
		
		if(!empty($params['Subsites'])) {
			// 'any' wasn't selected
			$subsiteIds = array();
			foreach($params['Subsites'] as $subsite) {
				if(is_numeric($subsite)) $subsiteIds[] = $subsite;
			}
			Subsite::$force_subsite = join(',', $subsiteIds);
		}
		
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
					$result->HasEmbargoOrExpiry = $wf->getEmbargoDate() || $wf->ExpiryDate() ? 'yes' : 'no';
					$doSet->push($result);
				}
			}
		}
		
		// Manually manage the subsite filtering
		if(ClassInfo::exists('Subsite')) Subsite::$force_subsite = null;
		
		return $doSet;
	}
	
	function columns() {
		$fields = array(
			'Title' => 'Title',
			'openWorkflowRequest.Author.Title' => 'Requested by',
			'openWorkflowRequest.Created' => 'Requested at',
			'HasEmbargoOrExpiry' => 'Embargo or expiry dates set',
			'ID' => array(
				'title' => 'Actions',
				'formatting' => '<a href=\"admin/show/$value\">Edit in CMS</a>'
			),
			'AbsoluteLink' => array(
				'title' => 'Links',
				'formatting' => '$value <a href=\"$value?stage=Live\">(live)</a> <a href=\"$value?stage=Stage\">(draft)</a>'
			)
		);
		
		if(class_exists('Subsite')) {
			$fields['Subsite.Title'] = 'Subsite';
		}
		
		return $fields;
	}
	
	function parameterFields() {
		$params = new FieldSet();
		
		if (class_exists('Subsite') && $subsites = DataObject::get('Subsite')) {
			$options = $subsites->toDropdownMap('ID', 'Title');
			array_unshift($options, 'Main site');
			$params->push(new CheckboxSetField('Subsites', 'Sites', $options));
		}
		
		return $params;
	}
	function canView() {
		return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
	}
}