<?php

class PublishingReport extends SSReport {
	function TreeTitle() {
		return 'Publishing activity';
	}
	function getReportField() {
		$fields = array(
			'PageTitle' => 'Page Title',
			'Published' => 'Published',
			'PublisherEmail' => 'Publisher',
			'AbsoluteLink' => 'URLs',
			'ID' => 'Edit',
		);
		
		if (class_exists('Subsite')) {
			$fields['SubsiteName'] = 'Subsite';
		}
		
		$tlf = new WorkflowRequestTableListField('ReportContent', 'SiteTree', $fields);
			
		$tlf->setFieldFormatting(array(
			'AbsoluteLink' => '$value <a href=\"$value?stage=Live\">(live)</a> <a href=\"$value?stage=Stage\">(draft)</a>',
			'ID' => '<a href=\"admin/show/$value\">Edit</a>',
		));
		
		$q = singleton('SiteTree')->extendedSQL();
		$q->select[] = 'SiteTree.Title AS PageTitle';
	
		$q->leftJoin('WorkflowRequest', 'WorkflowRequest.PageID = SiteTree.ID');
		$q->select[] = "WorkflowRequest.LastEdited as Published";
		$q->where[] = "WorkflowRequest.ClassName = 'WorkflowPublicationRequest'";
		
		
		$q->leftJoin('Member', 'WorkflowRequest.PublisherID = Member.ID');
		$q->select[] = 'Member.Email as PublisherEmail';
		
		if (class_exists('Subsite')) {
			$q->leftJoin('Subsite', 'SiteTree.SubsiteID = Subsite.ID');
			$q->select[] = 'Subsite.Title as SubsiteName';
		}
		
		// restrict to member id
		if (isset($_REQUEST['member']) && DataObject::get_by_id('Member', "Email = '".Convert::raw2sql($_REQUEST['member'])."'")) {
			$q->where[] = "Member.Email = '".Convert::raw2sql($_REQUEST['member']."'");
		}
		
		// restrict to subsite id
		if (class_exists('Subsite') && isset($_REQUEST['subsiteId']) && DataObject::get_by_id('Subsite', $_REQUEST['subsiteId'])) {
			$q->where[] = "SiteTree.SubsiteID = ".Convert::raw2sql($_REQUEST['subsiteId']);
		}
		
		// restrict by time period
		if (isset($_REQUEST['howFarBack'])) {
			switch ($_REQUEST['howFarBack']) {
				case '1hour':
					$q->where[] = "WorkflowRequest.LastEdited >= '".date('Y-m-d', time()-3600)."'";
					break;
				case '1day':
					$q->where[] = "WorkflowRequest.LastEdited >= '".date('Y-m-d', time()-3600*24)."'";
					break;
				case '1week':
					$q->where[] = "WorkflowRequest.LastEdited >= '".date('Y-m-d', time()-3600*24*7)."'";
					break;
			}
		}
		
		$tlf->setCustomQuery($q);
		$tlf->actions = array();
		$tlf->disableSorting(true);
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
		
		$options = DataObject::get('Member')->toDropdownMap('ID', 'Name');
		array_unshift($options, 'Any');
		$params->push(new DropdownField(
			"memberId", 
			"Member", 
			$options
		));
		
		$params->push(new DropdownField(
			"howFarBack", 
			"Time period", 
			array(
				'1hour' => 'Within the last hour',
				'1day' => 'Within the last day',
				'1week' => 'Within the last week'
			)
		));
		
		return $fields;
	}
	
	function getCMSActions() {
		$actions = parent::getCMSActions();
		$actions->push(new FormAction('updatereport', 'Search'));
		return $actions;
	}
}