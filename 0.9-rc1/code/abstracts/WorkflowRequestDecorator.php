<?php

abstract class WorkflowRequestDecorator extends DataObjectDecorator {
	
	abstract function notifyAwaitingApproval($comment);
	abstract function notifyComment($comment);
	abstract function WorkflowActions();
	abstract function saveAndPublish($comment, $member = null, $notify = true);
}