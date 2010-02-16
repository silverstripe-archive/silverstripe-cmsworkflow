<?php

class BatchSetExpiry extends CMSBatchAction {
	function getActionTitle() {
		return _t('BatchSetExpiry.ACTION_TITLE', 'Set expiry date');
	}
	function getDoingText() {
		return _t('BatchSetExpiry.DOING_TEXT', 'Setting expiry date');
	}

	function run(DataObjectSet $pages) {
		return $this->batchaction($pages, 'setExpiry',
			_t('BatchSetExpiry.ACTIONED_PAGES', 'Set expiry date on %d pages, %d failures'),
		array($_REQUEST['ExpiryDate_Batch']['Date'], $_REQUEST['ExpiryDate_Batch']['Time']));
	}
	
	function getParameterFields() {
		return new Fieldset(
			new PopupDateTimeField('ExpiryDate_Batch')
		);
	}

	function applicablePages($ids) {
		return $this->applicablePagesHelper($ids, 'canChangeExpiry', true, true);
	}
}
