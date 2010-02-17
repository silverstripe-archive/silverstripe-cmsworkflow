<?php

class BatchSetEmbargo extends CMSBatchAction {
	function getActionTitle() {
		return _t('BatchSetEmbargo.ACTION_TITLE', 'Set embargo date');
	}
	function getDoingText() {
		return _t('BatchSetEmbargo.DOING_TEXT', 'Setting embargo date');
	}

	function run(DataObjectSet $pages) {
		$tzConverter = new TZDateTimeField('TZConvert', $_REQUEST['EmbargoDate_Batch'], SiteConfig::current_site_config()->Timezone);
		$tzConverter->setValue($_REQUEST['EmbargoDate_Batch']);
		$date = date('d/m/Y', strtotime($tzConverter->Value()));
		$time = date('h:i a', strtotime($tzConverter->Value()));
		return $this->batchaction($pages, 'setEmbargo',
			_t('BatchSetEmbargo.ACTIONED_PAGES', 'Set embargo date on %d pages, %d failures'),
		array($date, $time));
	}
	
	function getParameterFields() {
		return new Fieldset(
			class_exists('TZDateTimeField') ? new TZDateTimeField('EmbargoDate_Batch') : new PopupDateTimeField('EmbargoDate_Batch')
		);
	}

	function applicablePages($ids) {
		return $this->applicablePagesHelper($ids, 'canChangeEmbargo', true, true);
	}
}
