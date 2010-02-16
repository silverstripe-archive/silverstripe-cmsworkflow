<?php

/**
 * Show all pages that need to be reviewed
 *
 * @package cmsworkflow
 * @subpackage reports
 */
class PagesDueForReviewReport extends SSReport {
	function title() {
		return 'Pages due for review';
	}
	
	function parameterFields() {
		$params = new FieldSet();
		
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
		
		return $params;
	}
	
	function columns() {
		$fields = array(
			'Title' => 'Page Title',
			'NextReviewDate' => 'Review Date',
			'Owner.Title' => 'Owner',
			'LastEditedBy.Title' => 'Last edited by',
			'OwnerID' => 'Owner ID',
			'AbsoluteLink' => array(
				'title' => 'URL',
				'formatting' => '$value <a href=\"$value?stage=Live\">(live)</a> <a href=\"$value?stage=Stage\">(draft)</a>',
			),
			'ID' => array(
				'Edit',
				'formatting' => '<a href=\"admin/show/$value\">Edit page</a>',
			),
		);
		
		if (class_exists('Subsite')) {
			$fields['Subsite.Title'] = 'Subsite';
		}
		
		return $fields;
	}
		
	function records($start, $limit, $params) {
		$wheres = array();

		if(isset($params['ReviewDate']) && $params['ReviewDate']) {
			list($day, $month, $year) = explode('/', $_REQUEST['ReviewDate']);
			$reviewDate = "$year-$month-$day";
			$wheres[] = 'NextReviewDate <= \'' . Convert::raw2sql($reviewDate) . '\'';
			
		} else {
			$wheres[] = 'NextReviewDate <= \'' . SSDatetime::now()->URLDate() . '\'';
		}
		
		if(isset($params['Owner']) && $params['Owner']) {
			$wheres[] = 'OwnerID = ' . (int)$params['Owner'];
		}
		
		if (class_exists('Subsite')) Subsite::$disable_subsite_filter = true;
		
		$limit = array(
			'start' => $start,
			'limit' => $limit,
		);
		
		$records = DataObject::get("SiteTree", join(' AND ', $wheres), "", "", $limit);
		
		if (class_exists('Subsite')) Subsite::$disable_subsite_filter = false;
		
		return $records;
	}
}