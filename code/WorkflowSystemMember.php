<?php

class WorkflowSystemMember extends Member
{
    public static $db = array();
    
    public static function get()
    {
        return DataObject::get_one('WorkflowSystemMember');
    }
    
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        if (!self::get()) {
            $su = new WorkflowSystemMember();
            $su->FirstName = 'CMS';
            $su->Surname = 'Workflow';
            $su->write();
            $su->addToGroupByCode('administrators');
            DB::alteration_message("Added CMS Workflow user", "created");
        }
    }
}
