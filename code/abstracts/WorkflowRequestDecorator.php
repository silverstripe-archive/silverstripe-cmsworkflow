<?php

abstract class WorkflowRequestDecorator extends DataObjectDecorator
{
    
    abstract public function notifyAwaitingApproval($comment);
    abstract public function notifyComment($comment);
    abstract public function WorkflowActions();
    abstract public function saveAndPublish($comment, $member = null, $notify = true);
}
