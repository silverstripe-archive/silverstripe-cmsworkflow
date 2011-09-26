<?php

class BatchSetEmbargo extends CMSBatchAction {
	function getActionTitle() {
		return _t('BatchSetEmbargo.ACTION_TITLE', 'Set embargo date');
	}
	function getDoingText() {
		return _t('BatchSetEmbargo.DOING_TEXT', 'Setting embargo date');
	}

	function run(DataObjectSet $pages) {
		$datefield = Object::create('DatetimeField', 'EmbargoDate_Batch');
		$datefield->setValue($_REQUEST['EmbargoDate_Batch']);
		$date = date('d/m/Y', strtotime($datefield->dataValue()));
		$time = date('h:i a', strtotime($datefield->dataValue()));
		return $this->batchaction($pages, 'setEmbargo',
			_t('BatchSetEmbargo.ACTIONED_PAGES', 'Set embargo date on %d pages, %d failures'),
		array($date, $time));
	}
	
	function getParameterFields() {
		return new FieldSet(Object::create('DatetimeField', 'EmbargoDate_Batch'));
	}

	function applicablePages($ids) {
		return $this->applicablePagesHelper($ids, 'canChangeEmbargo', true, true);
	}
}
