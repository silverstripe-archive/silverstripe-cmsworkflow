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
		
		// We need to be a bit fancier when subsites is enabled
		if(class_exists('Subsite') && $subsites = DataObject::get('Subsite')) {
			// javascript for subsite specific owner dropdown
			Requirements::javascript('cmsworkflow/javascript/PagesDueForReviewReport.js');

			// Remember current subsite
			$existingSubsite = Subsite::currentSubsiteID();
			
			// Create subsite specific owner dropdowns
			foreach($options as $option => $dummy) {
				if($option == 0) {
					Subsite::$disable_subsite_filter = true;
				} else {
					Subsite::changeSubsite($option);
				}
				
				$cmsUsers = Permission::get_members_by_permission(array("CMS_ACCESS_CMSMain", "ADMIN"));
				$map = $cmsUsers->map('ID', 'Title', '(no owner)');
				unset($map['']);
				$map = array('' => 'Any', '-1' => '(no owner)') + $map;
				
				$dropdown = new DropdownField("OwnerID" . $option, 'Page owner', $map);
				$dropdown->addExtraClass('subsiteSpecificOwnerID');
				$params->push($dropdown);
				
				if($option == 0) {
					Subsite::$disable_subsite_filter = false;
				}
			}
			
			// Restore current subsite
			Subsite::changeSubsite($existingSubsite);
		} else {
			$cmsUsers = Permission::get_members_by_permission(array("CMS_ACCESS_CMSMain", "ADMIN"));
			$map = $cmsUsers->map('ID', 'Title', '(no owner)');
			unset($map['']);
			$map = array('' => 'Any', '-1' => '(no owner)') + $map;
			$params->push(new DropdownField("OwnerID", 'Page owner', $map));
		}
		
		$params->push(new CalendarDateField('ReviewDateAfter', 'Review date after or on (DD/MM/YYYY)'));
		$params->push(new CalendarDateField('ReviewDateBefore', 'Review date before or on (DD/MM/YYYY)'));

		$params->push(new CheckboxField('ShowVirtualPages', 'Show Virtual Pages'));
		
		return $params;
	}
	
	function columns() {
		$fields = array(
			'Title' => 'Page Title',
			'NextReviewDate' => array(
				'title' => 'Review Date',
				'casting' => 'Date->Full'
			),
			'Owner.Title' => 'Owner',
			'LastEditedBy.Title' => 'Last edited by',
			'AbsoluteLink' => array(
				'title' => 'URL',
				'formatting' => '<a href=\"admin/show/$ID\" title=\"Edit page\">$value</a> " . ($AbsoluteLiveLink ? "<a href=\"$AbsoluteLiveLink\">(live)</a>" : "") . " <a href=\"$value?stage=Stage\">(draft)</a>',
			)
		);
		
		return $fields;
	}
		
	function sourceQuery($params) {
		$wheres = array();
		
		
		if(empty($params['ReviewDateBefore']) && empty($params['ReviewDateAfter'])) {
			// If there's no review dates set, default to all pages due for review now
			$wheres[] = 'NextReviewDate < \'' . SSDatetime::now()->URLDate() . '\' + INTERVAL 1 DAY';
		} else {
			// Review date before
			if(!empty($params['ReviewDateBefore'])) {
				list($day, $month, $year) = explode('/', $_REQUEST['ReviewDateBefore']);
				$reviewDate = "$year-$month-$day";
				$wheres[] = 'NextReviewDate < \'' . Convert::raw2sql($reviewDate) . '\' + INTERVAL 1 DAY';
			}
			
			// Review date after
			if(!empty($params['ReviewDateAfter'])) {
				list($day, $month, $year) = explode('/', $_REQUEST['ReviewDateAfter']);
				$reviewDate = "$year-$month-$day";
				$wheres[] = 'NextReviewDate >= \'' . Convert::raw2sql($reviewDate) . '\'';
			}
		}
		

		
		// Show virtual pages?
		if(empty($params['ShowVirtualPages'])) {
			$wheres[] = "ClassName != 'VirtualPage' AND ClassName != 'SubsitesVirtualPage'";
		}
		
		// We use different dropdown depending on the subsite
		$ownerIdParam = 'OwnerID';

		
		// Owner dropdown
		if(!empty($params[$ownerIdParam])) {
			$ownerID = (int)$params[$ownerIdParam];
			// We use -1 here to distinguish between No Owner and Any
			if($ownerID == -1) $ownerID = 0;
			$wheres[] = 'OwnerID = ' . $ownerID;
		}
		
		$query = singleton("SiteTree")->extendedSQL(join(' AND ', $wheres));
		$query->select[] = '(SELECT "URLSegment" FROM "SiteTree_Live" WHERE "SiteTree_Live"."ID" = "SiteTree"."ID") AS LiveURLSegment';
		
		return $query;
	}
}

?>
