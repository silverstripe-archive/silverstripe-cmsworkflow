<?php
/**
 * @package cmsworkflow
 * @subpackage tests
 */
class ThreeStepWorkflowReportsTest extends FunctionalTest
{
    
    protected $illegalExtensions = array(
        'SiteTree' => array('SiteTreeCMSTwoStepWorkflow'),
        'SiteConfig' => array('SiteConfigTwoStepWorkflow'),
        'WorkflowRequest' => array('WorkflowTwoStepRequest'),
    );

    protected $requiredExtensions = array(
        'SiteTree' => array('SiteTreeCMSThreeStepWorkflow'),
        'WorkflowRequest' => array('WorkflowThreeStepRequest'),
        'LeftAndMain' => array('LeftAndMainCMSThreeStepWorkflow'),
        'SiteConfig' => array('SiteConfigThreeStepWorkflow'),
    );
    
    public static $extensionsToReapply = array();
    public static $extensionsToRemoveAfter = array();
    
    public function testBasicSanity()
    {
        $reports = array(
            'ApprovedDeletions3StepReport', 'ApprovedPublications3StepReport',
            'UnapprovedDeletions3StepReport', 'UnapprovedPublications3StepReport'
        );
        foreach ($reports as $class) {
            $report = new $class;
            $this->assertTrue($report->canView());
            $this->assertTrue(is_string($report->group()));
            $this->assertTrue(is_string($report->title()));
            $this->assertTrue(is_array($report->columns()));
            $fields = $report->parameterFields();
            if ($fields) {
                $this->assertTrue($fields instanceof FieldSet);
            }
        }
    }
}
