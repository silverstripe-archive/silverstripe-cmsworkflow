<?php

class BatchPublishPages extends CMSBatchAction {
	function getActionTitle() {
		return _t('BatchPublishPages.PUBLISH_PAGES', 'CMS Workflow Publish pages');
	}
	function getDoingText() {
		return _t('BatchPublishPages.PUBLISHING_PAGES', 'Publishing pages');
	}

	function run(DataObjectSet $pages) {
		$pageIDs = $pages->column('ID');

		foreach($pageIDs as $pageID) FormResponse::add("$('Form_EditForm').reloadIfSetTo($pageID);");

		$failures = 0;
		foreach($pages as $page) {
			// Is the action a deletion or a publication
			if($wr = $page->openWorkflowRequest('WorkflowRequest')) {
				
			}

			// Perform the action
			if (call_user_func_array(array($page, $helperMethod), $arguments) === false) $failures++;
			
			// Now make sure the tree title is appropriately updated
			$publishedRecord = DataObject::get_by_id('SiteTree', $page->ID);
			if ($publishedRecord) {
				$JS_title = Convert::raw2js($publishedRecord->TreeTitle());
				FormResponse::add("\$('sitetree').setNodeTitle($page->ID, '$JS_title');");
			}
			$page->destroy();
			unset($page);
		}

		$message = sprintf($successMessage, $pages->Count()-$failures, $failures);
		FormResponse::add('statusMessage("'.$message.'","good");');

		return FormResponse::respond();

		
		$this->batchaction($pages, 'batchPublish',
			_t('BatchPublishPages.PUBLISHED_PAGES', 'Published %d pages, %d failures')
		);

		return FormResponse::respond();
	}
}


