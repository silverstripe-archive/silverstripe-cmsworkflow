<?php

class BatchResetExpiry extends CMSBatchAction {
	function getActionTitle() {
		return _t('BatchResetExpiry.ACTION_TITLE', 'Reset expiry date');
	}
	function getDoingText() {
		return _t('BatchResetExpiry.DOING_TEXT', 'Resetting expiry date');
	}

	function run(DataObjectSet $pages) {
		return $this->batchaction($pages, 'resetExpiry',
			_t('BatchResetExpiry.ACTIONED_PAGES', 'Reset expiry date on %d pages, %d failures'));
	}

	function applicablePages($ids) {
		return $this->applicablePagesHelper($ids, 'canChangeExpiry', true, true);
	}
}
