<?php

class BatchSetExpiry extends CMSBatchAction {
	function getActionTitle() {
		return _t('BatchSetExpiry.ACTION_TITLE', 'Set expiry date');
	}
	function getDoingText() {
		return _t('BatchSetExpiry.DOING_TEXT', 'Setting expiry date');
	}

	function run(DataObjectSet $pages) {
		if(class_exists('TZDateTimeField')) $datefield = new TZDateTimeField('TZConvert', $_REQUEST['ExpiryDate_Batch'], SiteConfig::current_site_config()->Timezone);
		else if(class_exists('PopupDateTimeField')) $datefield = new PopupDateTimeField('ExpiryDate_Batch');
		else $datefield = new DateTimeField('EmbargoDate_Batch');

		$datefield->setValue($_REQUEST['ExpiryDate_Batch']);
		
		$date = date('d/m/Y', strtotime($datefield->Value()));
		$time = date('h:i a', strtotime($datefield->Value()));
		return $this->batchaction($pages, 'setExpiry',
			_t('BatchSetExpiry.ACTIONED_PAGES', 'Set expiry date on %d pages, %d failures'),
		array($date, $time));
	}
	
	function getParameterFields() {
		if(class_exists('TZDateTimeField'))	$dateField = new TZDateTimeField('ExpiryDate_Batch');
		else $dateField = new DatetimeField('ExpiryDate_Batch');
		return new FieldSet($dateField);
	}
	
	function confirmationDialog($ids) {
		$pagesWithBacklinks = array();
		foreach($ids as $id) {
			$page = DataObject::get_by_id('SiteTree', $id);
			if ($page->BacklinkTracking()->Count()) $pagesWithBacklinks[] = $page->AbsoluteLink();
		}
		
		return array(
			'alert' => count($pagesWithBacklinks) ? true : false,
			'content' => 'The following pages will create broken links when they expire:'."\n\n".
							join("\n", $pagesWithBacklinks)."\n\nProceed?"
		);
	}

	function applicablePages($ids) {
		return $this->applicablePagesHelper($ids, 'canChangeExpiry', true, true);
	}
}
