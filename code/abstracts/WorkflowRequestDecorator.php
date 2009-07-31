<?php

abstract class WorkflowRequestDecorator extends DataObjectDecorator {
	protected $memberIdsEmailed = array();
	
	abstract function notifyAwaitingApproval($comment);
	abstract function notifyComment($comment);
	abstract function WorkflowActions();
	abstract function saveAndPublish($comment, $member = null, $notify = true);
	
	/**
	 * Add a member to the 'i've emailed them' list
	 *
	 * @param Member $member 
	 */
	final public function addMemberEmailed(Member $member) {
		$this->memberIdsEmailed[] = (int)$member->ID;
	}
	
	/**
	 * Get a list of people emails this http request
	 *
	 * @return DataObjectSet
	 */
	final public function getMembersEmailed() {
		$doSet = new DataObjectSet();
		foreach(array_unique($this->memberIdsEmailed) as $id) {
			$doSet->push(DataObject::get_by_id('Member', $id));
		}
		return $doSet;
	}
	
	/**
	 * Clear the list of people emailed this http request
	 *
	 * @return void
	 */
	final public function clearMembersEmailed() {
		$this->memberIdsEmailed = array();
	}
}