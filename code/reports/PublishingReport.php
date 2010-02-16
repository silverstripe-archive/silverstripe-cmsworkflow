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
				'casting' => 'SSDatetime->Full'
			),
			'PublisherTitle' => 'Publisher',
			'AbsoluteLink' => array(
				'title' => 'Links',
				'formatting' => '$value " . ($AbsoluteLiveLink ? "<a href=\"$AbsoluteLiveLink\">(live)</a>" : "") . " <a href=\"$value?stage=Stage\">(draft)</a>'
			),
			'ExpiryDate' => array(
				'title' => 'Expiry on?',
				'formatting' => '" . ($value && $value != "0000-00-00 00:00:00" ? "yes" : "no") . "'
			)
		);
		return $fields;
	}
	
	function sourceQuery($params) {
		$q = singleton('SiteTree')->extendedSQL();
		$q->select[] = 'SiteTree.Title AS PageTitle';
	
		$q->leftJoin('WorkflowRequest', 'WorkflowRequest.PageID = SiteTree.ID');
		$q->select[] = "WorkflowRequest.LastEdited as Published";
		$q->where[] = "WorkflowRequest.ClassName = 'WorkflowPublicationRequest'";
		$q->where[] = "WorkflowRequest.Status = 'Completed'";
		
		
		$q->leftJoin('Member', 'WorkflowRequest.PublisherID = Member.ID');
		$q->select[] = Member::get_title_sql().' as PublisherTitle';
		
		// restrict to member id
		if (!empty($params['memberId']) && DataObject::get_by_id('Member', $params['memberId'])) {
			$q->where[] = "Member.ID = ".Convert::raw2sql($params['memberId']);
		}
		
		$startDate = isset($params['StartDate']) ? $params['StartDate'] : null;
		$endDate = isset($params['EndDate']) ? $params['EndDate'] : null;
		
		if($startDate) {
			if(count(explode('/', $startDate['Date'])) == 3) {
				list($d, $m, $y) = explode('/', $startDate['Date']);
				$startDate['Time'] = $startDate['Time'] ? $startDate['Time'] : '00:00:00';
				$startDate = @date('Y-m-d H:i:s', strtotime("$y-$m-$d {$startDate['Time']}"));
			} else {
				$startDate = null;
			}
		}
		
		if($endDate) {
			if(count(explode('/', $endDate['Date'])) == 3) {
				list($d,$m,$y) = explode('/', $endDate['Date']);
				$endDate['Time'] = $endDate['Time'] ? $endDate['Time'] : '23:59:59';
				$endDate = @date('Y-m-d H:i:s', strtotime("$y-$m-$d {$endDate['Time']}"));
			} else {
				$endDate = null;
			}
		}
		
		if ($startDate && $endDate) {
			$q->where[] = "WorkflowRequest.LastEdited >= '".Convert::raw2sql($startDate)."' AND WorkflowRequest.LastEdited <= '".Convert::raw2sql($endDate)."'";
		} else if ($startDate && !$endDate) {
			$q->where[] = "WorkflowRequest.LastEdited >= '".Convert::raw2sql($startDate)."'";
		} else if (!$startDate && $endDate) {
			$q->where[] = "WorkflowRequest.LastEdited <= '".Convert::raw2sql($endDate)."'";
		} else {
			$q->where[] = "WorkflowRequest.LastEdited >= '".SSDatetime::now()->URLDate()."'";
		}
		
		return $q;
	}
	
	function parameterFields() {
		$params = new FieldSet();

		$options = DataObject::get('Member')->toDropdownMap('ID', 'Name', 'Any');
		$params->push(new DropdownField(
			"memberId", 
			"Member", 
			$options
		));
		
		$params->push($startDate = new PopupDateTimeField('StartDate', 'Start date'));
		$params->push($endDate = new PopupDateTimeField('EndDate', 'End date'));
		$endDate->setValue(array('Date' => null, 'Time' => '11:59 pm'));
		$startDate->setValue(array('Date' => null, 'Time' => '12:00 am'));
		
		return $params;
	}
}
