<?php

/**
 * Show all pages that need to be reviewed
 *
 * @package cmsworkflow
 * @subpackage reports
 */
class PagesDueForReviewReport extends SSReport {
	function TreeTitle() {
		return 'Pages due for review';
	}
	function getReportField() {
		$fields = array(
			'Title' => 'Page Title',
			'NextReviewDate' => 'Review Date',
			'Owner.Title' => 'Owner',
			'OwnerID' => 'Owner ID',
			'AbsoluteLink' => 'URL',
			'ID' => 'Edit'
		);
		
		if (class_exists('Subsite')) {
			$fields['Subsite.Title'] = 'Subsite';
		}

		$wheres = array();
			
		if(isset($_REQUEST['ReviewDate']) && $_REQUEST['ReviewDate']) {
			list($day, $month, $year) = explode('/', $_REQUEST['ReviewDate']);
			$reviewDate = "$year-$month-$day";
			$wheres[] = 'NextReviewDate <= \'' . Convert::raw2sql($reviewDate) . '\'';
			
		} else {
			$wheres[] = 'NextReviewDate <= \'' . SSDatetime::now()->URLDate() . '\'';
		}
		
		if(isset($_REQUEST['Owner']) && $_REQUEST['Owner']) {
			$wheres[] = 'OwnerID = ' . (int) $_REQUEST['Owner'];
		}

		$tlf = new WorkflowRequestTableListField('ReportContent', 'SiteTree', $fields, join(' AND ', $wheres));
			
		$tlf->setFieldFormatting(array(
			'AbsoluteLink' => '$value <a href=\"$value?stage=Live\">(live)</a> <a href=\"$value?stage=Stage\">(draft)</a>',
			'ID' => '<a href=\"admin/show/$value\">Edit page</a>'
		));

		return $tlf;
	}
	
	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->insertBefore($params = new Tab('Search Parameters'), 'Report');
		
		if (class_exists('Subsite') && $subsites = DataObject::get('Subsite')) {
			$options = $subsites->toDropdownMap('ID', 'Title');
			array_unshift($options, 'Any');
			$params->push(new DropdownField(
				"subsiteId", 
				"Subsite", 
				$options
			));
		}
		
		$cmsUsers = Permission::get_members_by_permission(array("CMS_ACCESS_CMSMain", "ADMIN"));
		$params->push(new CalendarDateField('ReviewDate', 'Review date (DD/MM/YYYY)', date('d/m/Y')));
		
		$params->push(new DropdownField("OwnerID", 'Page owner', $cmsUsers->map('ID', 'Title', '(no owner)')));
		
		return $fields;
	}
	
	function getCMSActions() {
		$actions = parent::getCMSActions();
		$actions->push(new FormAction('updatereport', 'Search'));
		return $actions;
	}
}