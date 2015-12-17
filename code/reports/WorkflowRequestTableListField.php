<?php

class WorkflowRequestTableListField extends TableListField
{
    public function Items()
    {
        if (class_exists('Subsite')) {
            Subsite::$disable_subsite_filter = true;
        }
        $items = parent::Items();
        if (class_exists('Subsite')) {
            Subsite::$disable_subsite_filter = false;
        }
        return $items;
    }
}
