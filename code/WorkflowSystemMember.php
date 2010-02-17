<?php

class WorkflowSystemMember extends Member {
	static $db = array();
	
	static function get() {
		return DataObject::get_one('WorkflowSystemMember');
	}
	
	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		if (!self::get()) {
			$su = new WorkflowSystemMember();
			$su->FirstName = 'CMS';
			$su->Surname = 'Workflow';
			$su->write();
			Group::addToGroupByName($su, 'administrators');
			SS_Database::alterationMessage("Added CMS Workflow user","created");
		}
	}
}