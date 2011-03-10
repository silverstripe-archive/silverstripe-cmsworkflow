<?php

class BatchSetEmbargo extends CMSBatchAction {
	function getActionTitle() {
		return _t('BatchSetEmbargo.ACTION_TITLE', 'Set embargo date');
	}
	function getDoingText() {
		return _t('BatchSetEmbargo.DOING_TEXT', 'Setting embargo date');
	}

	function run(DataObjectSet $pages) {
		if(class_exists('TZDateTimeField')) $datefield = new TZDateTimeField('TZConvert', $_REQUEST['EmbargoDate_Batch'], SiteConfig::current_site_config()->Timezone);
		else if(class_exists('PopupDateTimeField')) $datefield = new PopupDateTimeField('EmbargoDate_Batch');
		else $datefield = new DateTimeField('EmbargoDate_Batch');

		$datefield->setValue($_REQUEST['EmbargoDate_Batch']);
		$date = date('d/m/Y', strtotime($datefield->dataValue()));
		$time = date('h:i a', strtotime($datefield->dataValue()));
		return $this->batchaction($pages, 'setEmbargo',
			_t('BatchSetEmbargo.ACTIONED_PAGES', 'Set embargo date on %d pages, %d failures'),
		array($date, $time));
	}
	
	function getParameterFields() {
		if(class_exists('TZDateTimeField')) $dateField = new TZDateTimeField('EmbargoDate_Batch');
		else $dateField = new DatetimeField('EmbargoDate_Batch');
		
		return new FieldSet($dateField);
	}

	function applicablePages($ids) {
		return $this->applicablePagesHelper($ids, 'canChangeEmbargo', true, true);
	}
}
