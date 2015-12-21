<?php

class BatchResetExpiry extends CMSBatchAction
{
    public function getActionTitle()
    {
        return _t('BatchResetExpiry.ACTION_TITLE', 'Reset expiry date');
    }
    public function getDoingText()
    {
        return _t('BatchResetExpiry.DOING_TEXT', 'Resetting expiry date');
    }

    public function run(DataObjectSet $pages)
    {
        return $this->batchaction($pages, 'resetExpiry',
            _t('BatchResetExpiry.ACTIONED_PAGES', 'Reset expiry date on %d pages, %d failures'));
    }

    public function applicablePages($ids)
    {
        return $this->applicablePagesHelper($ids, 'canChangeExpiry', true, true);
    }
}
