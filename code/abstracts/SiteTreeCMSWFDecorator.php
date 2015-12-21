<?php

abstract class SiteTreeCMSWFDecorator extends DataObjectDecorator
{
    abstract public function canDenyRequests();
    abstract public function canRequestEdit();
    abstract public function whoCanApprove();
    abstract public function getOpenRequest($workflowClass);
}
