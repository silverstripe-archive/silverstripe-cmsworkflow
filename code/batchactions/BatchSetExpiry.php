<?php

class BatchSetExpiry extends CMSBatchAction {
	function getActionTitle() {
		return _t('BatchSetExpiry.ACTION_TITLE', 'Set expiry date');
	}
	function getDoingText() {
		return _t('BatchSetExpiry.DOING_TEXT', 'Setting expiry date');
	}

	function run(DataObjectSet $pages) {
		$tzConverter = new TZDateTimeField('TZConvert', $_REQUEST['ExpiryDate_Batch'], SiteConfig::current_site_config()->Timezone);
		$tzConverter->setValue($_REQUEST['ExpiryDate_Batch']);
		$date = date('d/m/Y', strtotime($tzConverter->Value()));
		$time = date('h:i a', strtotime($tzConverter->Value()));
		return $this->batchaction($pages, 'setExpiry',
			_t('BatchSetExpiry.ACTIONED_PAGES', 'Set expiry date on %d pages, %d failures'),
		array($date, $time));
	}
	
	function getParameterFields() {
		return new Fieldset(
			new TZDateTimeField('ExpiryDate_Batch')
		);
	}

	function applicablePages($ids) {
		return $this->applicablePagesHelper($ids, 'canChangeExpiry', true, true);
	}
}
