<?php

class BatchApprovePages extends CMSBatchAction {
	function getActionTitle() {
		return _t('BatchApprovePages.APPROVE_PAGES', 'CMS Workflow Approve pages');
	}
	function getDoingText() {
		return _t('BatchApprovePages.APPROVING_PAGES', 'Approving pages');
	}

	function run(DataObjectSet $pages) {
		return $this->batchaction($pages, 'batchApprove',
			_t('BatchApprovePages.APPROVED_PAGES', 'Approved %d pages')
		);
	}
}
