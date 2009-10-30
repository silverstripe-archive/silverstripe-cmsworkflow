<?php

class BatchSetEmbargo extends CMSBatchAction {
	function getActionTitle() {
		return _t('BatchSetEmbargo.ACTION_TITLE', 'Set embargo date');
	}
	function getDoingText() {
		return _t('BatchSetEmbargo.DOING_TEXT', 'Setting embargo date');
	}

	function run(DataObjectSet $pages) {
		return $this->batchaction($pages, 'setEmbargo',
			_t('BatchSetEmbargo.ACTIONED_PAGES', 'Set embargo date on %d pages, %d failures'),
		array($_REQUEST['EmbargoDate_Batch']['Date'], $_REQUEST['EmbargoDate_Batch']['Time']));
	}
	
	function getParameterFields() {
		return new Fieldset(
			new PopupDateTimeField('EmbargoDate_Batch')
		);
	}
}
