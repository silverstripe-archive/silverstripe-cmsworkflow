<?php

class WorkflowRequestTableListField extends TableListField {
	function Items() {
		if (class_exists('Subsite')) Subsite::$disable_subsite_filter = true;
		$fieldItems = new DataObjectSet();
		if($items = $this->sourceItems()) foreach($items as $item) {
			$fieldItem = new TableListField_Item($item, $this);
			if($item) $fieldItems->push(new TableListField_Item($item, $this));
		}
		if (class_exists('Subsite')) Subsite::$disable_subsite_filter = false;
		return $fieldItems;
	}
}