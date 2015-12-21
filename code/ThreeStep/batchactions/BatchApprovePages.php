<?php

class BatchApprovePages extends CMSBatchAction
{
    public function getActionTitle()
    {
        return _t('BatchApprovePages.APPROVE_PAGES', 'Approve');
    }
    public function getDoingText()
    {
        return _t('BatchApprovePages.APPROVING_PAGES', 'Approving pages');
    }

    public function run(DataObjectSet $pages)
    {
        $pageIDs = $pages->column('ID');
        foreach ($pageIDs as $pageID) {
            FormResponse::add("$('Form_EditForm').reloadIfSetTo($pageID);");
        }
        
        $this->batchaction($pages, 'batchApprove',
            _t('BatchApprovePages.APPROVED_PAGES', 'Approved %d pages, %d failures')
        );
        
        return FormResponse::respond();
    }

    public function applicablePages($ids)
    {
        return $this->applicablePagesHelper($ids, 'canBatchApprove', true, true);
    }
}
