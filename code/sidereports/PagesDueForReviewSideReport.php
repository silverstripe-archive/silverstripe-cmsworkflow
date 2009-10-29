<?php

/**
 * Report to show pages that will be due for review soon
 *
 * @package cmsworkflow
 * @subpackage reports
 */
class PagesDueForReviewSideReport extends SideReport {
	function title() {
		return _t('PagesDueForReviewSideReport.TITLE', 'Pages due for review');
	}
	function group() {
		return "Content reports";
	}
	function sort() {
		return -10;
	}
	function records() {
		$where =	array();
	
		if(isset($this->params['ReviewDate']) && $this->params['ReviewDate']) {
			$where[] = 'NextReviewDate <= \'' . Convert::raw2sql($this->params['ReviewDate']) . '\'';
		} else {
			$where[] = 'NextReviewDate <= \'' . SS_Datetime::now()->URLDate() . '\'';
		}
		
		if(isset($this->params['OwnerID'])) {
			switch($this->params['OwnerID']) {
				case 'any-owner':
					break;
				case 'no-owner':
					$where[] = 'OwnerID = 0';
				default:
					$where[] = 'OwnerID = ' . (int) $this->params['OwnerID'];
					break;
			}
		}
		
		return DataObject::get('SiteTree', $where);
	}
	
	function fieldsToShow() {
		return array(
			"Title" => array(
				"source" => array("NestedTitle", array("2")),
				"link" => true,
			),
			"Date" => array(
				"prefix" => 'Due for review on ',
				"source" => "NextReviewDate",
			),
			"Owner" => array(
				"prefix" => ', owned by ',
				"source" => "OwnerName"
			)
		);
	}
	
	function getParameterFields() {
		$cmsUsers = Permission::get_members_by_permission(array("CMS_ACCESS_CMSMain", "ADMIN"));
		
		$options = array(
			'any-owner' => 'Any owner',
			'no-owner' => 'No owner'
		);
		
		$options = array_merge($options, $cmsUsers->map('ID', 'Title'));
		
		return new FieldSet(
			new DateField('ReviewDate', 'Review date (YYYY-MM-DD)'),
			new DropdownField("OwnerID", 'Page owner', $options)
		);
	}
}

?>
