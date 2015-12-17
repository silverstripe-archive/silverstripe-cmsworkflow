<?php

class BatchResetEmbargo extends CMSBatchAction
{
    public function getActionTitle()
    {
        return _t('BatchResetEmbargo.ACTION_TITLE', 'Reset embargo date');
    }
    public function getDoingText()
    {
        return _t('BatchResetEmbargo.DOING_TEXT', 'Resetting embargo date');
    }

    public function run(DataObjectSet $pages)
    {
        return $this->batchaction($pages, 'resetEmbargo',
            _t('BatchResetEmbargo.ACTIONED_PAGES', 'Reset embargo date on %d pages, %d failures'));
    }

    public function applicablePages($ids)
    {
        return $this->applicablePagesHelper($ids, 'canChangeEmbargo', true, true);
    }
}
