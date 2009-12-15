<?php

abstract class SiteTreeCMSWFDecorator extends DataObjectDecorator {
	abstract function canDenyRequests();
	abstract function canRequestEdit();
	abstract function whoCanApprove();
	abstract function getOpenRequest($workflowClass);
}