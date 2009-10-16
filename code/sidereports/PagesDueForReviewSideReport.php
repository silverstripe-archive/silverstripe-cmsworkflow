<?php

class PagesDueForReviewSideReport extends SideReport {
	function title() {
		return _t('PagesDueForReviewSideReport.TITLE', 'Pages due for review');
	}
	
	function records() {
		$where =	array();
	
		if(isset($this->params['ReviewDate']) && $this->params['ReviewDate']) {
			$where[] = 'NextReviewDate < \'' . Convert::raw2sql($this->params['ReviewDate']) . '\'';
		} else {
			$where[] = 'NextReviewDate < \'' . SSDatetime::now()->URLDate() . '\'';
		}
		
		if(isset($this->params['Owner']) && $this->params['Owner']) {
			$where[] = 'OwnerID = ' . (int) $this->params['Owner'];
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
		
		return new FieldSet(
			new DateField('ReviewDate', 'Review date (YYYY-MM-DD)'),
			new DropdownField("OwnerID", 'Page owner', $cmsUsers->map('ID', 'Title', '(no owner)'))
		);
	}
}

?>
