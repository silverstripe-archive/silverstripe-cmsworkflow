<?php
/**
 * @package cmsworkflow
 */
class WorkflowMemberRole extends DataObjectDecorator {
	
	function extraStatics() {
		return array(
			'has_many' => array(
				'AuthoredPublicationRequests' => 'WorkflowPublicationRequest',
				'AuthoredDeletionRequests' => 'WorkflowDeletionRequest',
			),
			'many_many' => array(
				'PublicationRequests' => 'WorkflowPublicationRequest',
				'DeletionRequests' => 'WorkflowDeletionRequest',
			)
		);
	}
	
	function updateCMSFields(&$fields) {
		$fields->removeByName('AuthoredPublicationRequests');
		$fields->removeByName('AuthoredDeletionRequests');
		$fields->removeByName('PublicationRequests');
		$fields->removeByName('DeletionRequests');
	}
}
?>