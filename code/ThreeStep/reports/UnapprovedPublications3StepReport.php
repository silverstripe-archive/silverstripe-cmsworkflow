<?php
/**
 * Report showing publication requests I need to approve
 * 
 * @package cmsworkflow
 * @subpackage ThreeStep
 */
class UnapprovedPublications3StepReport extends SS_Report
{

    /**
     * @var Array
     */
    protected $_cache_sourceRecords = array();
    
    public function title()
    {
        return _t('UnapprovedPublications3StepReport.TITLE', "Publication requests I need to approve");
    }
    
    public function sourceRecords($params, $sort, $limit)
    {
        $cachekey = md5(serialize($params));
        if (!isset($this->_cache_sourceRecords[$cachekey])) {
            $res = WorkflowThreeStepRequest::get_by_approver(
                'WorkflowPublicationRequest',
                Member::currentUser(),
                array('AwaitingApproval')
            );
        
            $doSet = new DataObjectSet();
            if ($res) {
                foreach ($res as $result) {
                    if ($wf = $result->openWorkflowRequest()) {
                        if (ClassInfo::exists('Subsite')) {
                            $result->SubsiteTitle = $result->Subsite()->Title;
                        }
                        $result->RequestedAt = $wf->Created;
                        $result->WFAuthorTitle = $wf->Author()->Title;
                        $result->HasEmbargo = $wf->getEmbargoDate();
                        $doSet->push($result);
                    }
                }
            }
            $this->_cache_sourceRecords[$cachekey] = $doSet;
        }
        
        $doSet = $this->_cache_sourceRecords[$cachekey];
        
        if ($sort) {
            $parts = explode(' ', $sort);
            $field = $parts[0];
            $direction = $parts[1];
            
            if ($field == 'AbsoluteLink') {
                $sort = 'URLSegment ' . $direction;
            }
            if ($field == 'Subsite.Title') {
                $sort = 'SubsiteID ' . $direction;
            }
            
            $doSet->sort($sort);
        }

        if ($limit && $limit['limit']) {
            return $doSet->getRange($limit['start'], $limit['limit']);
        } else {
            return $doSet;
        }
    }
    
    public function columns()
    {
        $fields = array(
            'Title' => array(
                'title' => 'Page name',
                'formatting' => '<a href=\"admin/show/$ID\" title=\"Edit page\">$value</a>'
            ),
            'WFAuthorTitle' => 'Requested by',
            'RequestedAt' => array(
                'title' => 'Requested',
                'casting' => 'SS_Datetime->Full'
            ),
            'HasEmbargo' => array(
                'title' => 'Embargo',
                'formatting' => '" . ($value ? date("j M Y g:ia", strtotime($value)) : "no") . "',
            ),
            'AbsoluteLink' => array(
                'title' => 'URL',
                'formatting' => '$value " . ($AbsoluteLiveLink ? "<a target=\"_blank\" href=\"$AbsoluteLiveLink\">(live)</a>" : "") . " <a target=\"_blank\" href=\"$value?stage=Stage\">(draft)</a>'
            )
        );
        
        return $fields;
    }

    /**
     * This alternative columns method is picked up by SideReportWrapper
     */
    public function sideReportColumns()
    {
        return array(
            'Title' => array(
                'link' => true,
            ),
            'WFAuthorTitle' => array(
                'formatting' => 'Requested by $value'
            ),
            'RequestedAt' => array(
                'formatting' => ' on $value',
                'casting' => 'SS_Datetime->Full'
            ),
        );
    }
    
    public function sortColumns()
    {
        return array(
            'SubsiteTitle',
            'WFAuthorTitle',
            'RequestedAt'
        );
    }
    
    public function group()
    {
        return _t('WorkflowRequest.WORKFLOW', 'Workflow');
    }

    public function canView()
    {
        return Object::has_extension('SiteTree', 'SiteTreeCMSThreeStepWorkflow');
    }
}
