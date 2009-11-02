<?php

class BatchApprovePages extends CMSBatchAction {
	function getActionTitle() {
		return _t('BatchApprovePages.APPROVE_PAGES', 'CMS Workflow Approve pages');
	}
	function getDoingText() {
		return _t('BatchApprovePages.APPROVING_PAGES', 'Approving pages');
	}

	function run(DataObjectSet $pages) {
		$pageIDs = $pages->column('ID');
		foreach($pageIDs as $pageID) FormResponse::add("$('Form_EditForm').reloadIfSetTo($pageID);");
		
		$this->batchaction($pages, 'batchApprove',
			_t('BatchApprovePages.APPROVED_PAGES', 'Approved %d pages, %d failures')
		);
		
		return FormResponse::respond();
	}
}
