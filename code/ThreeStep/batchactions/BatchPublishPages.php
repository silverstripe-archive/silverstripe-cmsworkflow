<?php

class BatchPublishPages extends CMSBatchAction {
	function getActionTitle() {
		return _t('BatchPublishPages.PUBLISH_PAGES', 'Publish');
	}
	function getDoingText() {
		return _t('BatchPublishPages.PUBLISHING_PAGES', 'Publishing pages');
	}

	function run(DataObjectSet $pages) {
		$pageIDs = $pages->column('ID');

		foreach($pageIDs as $pageID) FormResponse::add("$('Form_EditForm').reloadIfSetTo($pageID);");

		$count = array();
		$count['PUBLISH_SUCCESS'] = $count['DELETE_SUCCESS'] = 0;
		$count['PUBLISH_FAILURE'] = $count['DELETE_FAILURE'] = 0;
		foreach($pages as $page) {
			$type = ($page->openWorkflowRequest() instanceof WorkflowDeletionRequest)
				? 'DELETE' : 'PUBLISH';
			
			if($page->batchPublish()) {
				$count[$type . '_SUCCESS']++;

				// Now make sure the tree title is appropriately updated
				$publishedRecord = DataObject::get_by_id('SiteTree', $page->ID);
				if ($publishedRecord) {
					$JS_title = Convert::raw2js($publishedRecord->TreeTitle());
					FormResponse::add("\$('sitetree').setNodeTitle($page->ID, '$JS_title');");
				}

			} else {
				$count[$type . '_FAILURE']++;
			}
			
			$page->destroy();
			unset($page);
		}
		
		$messages = array(
			'PUBLISH_SUCCESS' => _t('BatchPublishPages.PUBLISH_SUCCESS', 'Published %d pages.'),
			'PUBLISH_FAILURE' => _t('BatchPublishPages.PUBLISH_FAILURE', 'Failed to publish %d pages.'),
			'DELETE_SUCCESS' => _t('BatchPublishPages.DELETE_SUCCESS', 'Deleted %d pages from the published site.'),
			'DELETE_FAILURE' => _t('BatchPublishPages.DELETE_FAILURE', 'Failed to delete %d pages from the published site.'),
			'PUBLISH_SUCCESS_ONE' => _t('BatchPublishPages.PUBLISH_SUCCESS_ONE', 'Published %d page.'),
			'PUBLISH_FAILURE_ONE' => _t('BatchPublishPages.PUBLISH_FAILURE_ONE', 'Failed to publish %d page.'),
			'DELETE_SUCCESS_ONE' => _t('BatchPublishPages.DELETE_SUCCESS_ONE', 'Deleted %d page from the published site.'),
			'DELETE_FAILURE_ONE' => _t('BatchPublishPages.DELETE_FAILURE_ONE', 'Failed to delete %d page from the published site.'),
		);

		$displayedMessages = array();
		foreach($count as $type => $count) {
			if($count) {
				$message = ($count==1) ? $messages[$type.'_ONE'] : $messages[$type];
				$displayedMessages[] = sprintf($message, $count);
			}
		}
		
		$displayedMessage = implode(" ", $displayedMessages);
		FormResponse::add('statusMessage("'.$displayedMessage.'","good");');

		return FormResponse::respond();
	}

	function applicablePages($ids) {
		return $this->applicablePagesHelper($ids, 'canBatchPublish', true, true);
	}
}

class BatchForcePublishPages extends CMSBatchAction_Publish {
	function getActionTitle() {
		return _t('BatchPublishPages.FORCE_PUBLISH', 'Force publish');
	}

	/**
	 * Only workflow admins should have access to this action
	 */
	function canView() {
		return Permission::check('IS_WORKFLOW_ADMIN');
	}
	
}