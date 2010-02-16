<?php

class PublishingReport extends SSReport {
	function title() {
		return 'Publishing activity';
	}
	
	function columns() {
		$fields = array(
			'PageTitle' => 'Page Title',
			'Published' => array(
				'title' => 'Published',
				'casting' => 'SSDatetime->Nice'
			),
			'PublisherEmail' => 'Publisher',
			'AbsoluteLink' => array(
				'title' => 'URLs',
				'formatting' => '$value <a href=\"$value?stage=Live\">(live)</a> <a href=\"$value?stage=Stage\">(draft)</a>',
			),
			'ID' => array(
				'title' => 'Edit',
				'formatting' => '<a href=\"admin/show/$value\">Edit</a> ',
			),
		);
		if (class_exists('Subsite')) {
			$fields['SubsiteName'] = 'Subsite';
		}
		return $fields;
	}
	
	function sourceQuery($params) {
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
		if (!empty($params['member']) && DataObject::get_by_id('Member', "Email = '".Convert::raw2sql($params['member'])."'")) {
			$q->where[] = "Member.Email = '".Convert::raw2sql($params['member']."'");
		}
		
		// restrict to subsite id
		if (class_exists('Subsite') && !empty($params['subsiteId']) && DataObject::get_by_id('Subsite', $params['subsiteId'])) {
			$q->where[] = "SiteTree.SubsiteID = ".Convert::raw2sql($params['subsiteId']);
		}
		
		// restrict by time period
		if (!empty ($params['howFarBack'])) {
			switch ($params['howFarBack']) {
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
		
		return $q;
	}
	
	function parameterFields() {
		$params = new FieldSet();
		
		if (class_exists('Subsite') && $subsites = DataObject::get('Subsite')) {
			$options = $subsites->toDropdownMap('ID', 'Title', 'Any');
			$params->push(new DropdownField(
				"subsiteId", 
				"Subsite", 
				$options
			));
		}
		
		$options = DataObject::get('Member')->toDropdownMap('ID', 'Name', 'Any');
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
		
		return $params;
	}
}
