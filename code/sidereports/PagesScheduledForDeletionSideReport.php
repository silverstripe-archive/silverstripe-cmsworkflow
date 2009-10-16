<?php
/**
 * Report to show pages scheduled to be deleted
 *
 * @package cmsworkflow
 * @subpackage reports
 */
class PagesScheduledForDeletionSideReport_ThisSubsite extends SideReport {
	function title() {
		return _t('PagesScheduledForDeletionSideReport.TITLE',"Workflow: pages scheduled for deletion");
	}
	function records() {
		$startDate = isset($this->params['StartDate']) ? $this->params['StartDate'] : null;
		$endDate = isset($this->params['EndDate']) ? $this->params['EndDate'] : null;
		if ($startDate && $endDate) {
			$where = "ExpiryDate >= '".Convert::raw2sql($startDate)."' AND ExpiryDate <= '".Convert::raw2sql($endDate)."'";
		} else if ($startDate && !$endDate) {
			$where = "ExpiryDate >= '".Convert::raw2sql($startDate)."'";
		} else if (!$startDate && $endDate) {
			$where = "ExpiryDate <= '".Convert::raw2sql($endDate)."'";
		} else {
			$where = "ExpiryDate >= '".SSDatetime::now()->URLDate()."'";
		}

		$doSet = Versioned::get_by_stage('SiteTree', 'Live', $where, 'ExpiryDate DESC');
		if ($doSet) {
			foreach($doSet as $do) {
				$do->HasBacklinks = $do->BackLinkTracking()->Count() ? ' HAS BLS' : false;
			}
		}
		return $doSet;
	}
	function fieldsToShow() {
		return array(
			"Title" => array(
				"source" => array("NestedTitle", array("2")),
				"link" => true,
			),
			"Requester" => array(
				"prefix" => 'Will be deleted at ',
				"source" => "ExpiryDate",
			),
			"HasBacklinks" => array(
				'source' => 'HasBacklinks'
			)
		);
	}
	function getParameterFields() {
		return new FieldSet(
			new DateField('StartDate', 'Start date (YYYY-MM-DD)'),
			new DateField('EndDate', 'End date (YYYY-MM-DD)')
		);
	}
}

class PagesScheduledForDeletionSideReport_AllSubsites extends SideReport {
	function title() {
		return _t('PagesScheduledForDeletionSideReport.TITLE_ALLSUBSITES',"Workflow: pages scheduled for deletion (all subsites)");
	}
	function records($params = null) {
		if (ClassInfo::exists('Subsite')) Subsite::$disable_subsite_filter = true;
		
		$startDate = isset($this->params['StartDate']) ? $this->params['StartDate'] : null;
		$endDate = isset($this->params['EndDate']) ? $this->params['EndDate'] : null;
		if ($startDate && $endDate) {
			$where = "ExpiryDate >= '".Convert::raw2sql($startDate)."' AND ExpiryDate <= '".Convert::raw2sql($endDate)."'";
		} else if ($startDate && !$endDate) {
			$where = "ExpiryDate >= '".Convert::raw2sql($startDate)."'";
		} else if (!$startDate && $endDate) {
			$where = "ExpiryDate <= '".Convert::raw2sql($endDate)."'";
		} else {
			$where = "ExpiryDate >= '".SSDatetime::now()->URLDate()."'";
		}
		
		$doSet = Versioned::get_by_stage('SiteTree', 'Live', $where, 'ExpiryDate DESC');
		if ($doSet) {
			foreach($doSet as $do) {
				$do->HasBacklinks = $do->BackLinkTracking()->Count() ? ' HAS BLS' : false;
			}
		}
		
		if (ClassInfo::exists('Subsite')) Subsite::$disable_subsite_filter = false;
		return $doSet;
	}
	function fieldsToShow() {
		return array(
			"Title" => array(
				"source" => array("NestedTitle", array("2")),
				"link" => true,
			),
			"Requester" => array(
				"prefix" => 'Will be deleted at ',
				"source" => "ExpiryDate",
			),
			"HasBacklinks" => array(
				'source' => 'HasBacklinks'
			)
		);
	}
	function getParameterFields() {
		return new FieldSet(
			new DateField('StartDate', 'Start date (YYYY-MM-DD)'),
			new DateField('EndDate', 'End date (YYYY-MM-DD)')
		);
	}
}

