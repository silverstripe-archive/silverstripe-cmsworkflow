<?php

global $lang;

$lang['en_US']['LeftAndMain']['CHANGEDURL'] = '  Changed URL to \'%s\'';
$lang['en_US']['LeftAndMain']['SAVEDUP'] = 'Saved';
$lang['en_US']['LeftAndMain']['STATUSTO'] = '  Status changed to \'%s\'';
$lang['en_US']['PublisherReviewSideReport']['TITLE'] = 'Workflow: Awaiting publication';
$lang['en_US']['SiteTree']['EDITANYONE'] = 'Anyone who can log-in to the CMS';
$lang['en_US']['SiteTree']['EDITONLYTHESE'] = 'Only these people (choose from list)';
$lang['en_US']['SiteTreeCMSWorkflow']['BUTTONREQUESTPUBLICATION'] = 'Request Publication';
$lang['en_US']['SiteTreeCMSWorkflow']['BUTTONREQUESTREMOVAL'] = 'Request Removal';
$lang['en_US']['SiteTreeCMSWorkflow']['CLOSEDREQUESTSHEADER'] = 'Closed Requests';
$lang['en_US']['SiteTreeCMSWorkflow']['COMMENT_MESSAGE'] = 'Commented on this workflow request. Emailed %s.';
$lang['en_US']['SiteTreeCMSWorkflow']['DENYPUBLICATION_MESSAGE'] = 'Denied workflow request, and reset content. Emailed %s';
$lang['en_US']['SiteTreeCMSWorkflow']['DIFFERENCESCOLUMN'] = 'Differences';
$lang['en_US']['SiteTreeCMSWorkflow']['DIFFERENCESLINK'] = 'Show differences to live';
$lang['en_US']['SiteTreeCMSWorkflow']['DIFFERENCESTHISCHANGECOLUMN'] = 'Differences in this change';
$lang['en_US']['SiteTreeCMSWorkflow']['DIFFERENCESTOLIVECOLUMN'] = 'Differences to live';
$lang['en_US']['SiteTreeCMSWorkflow']['FIELDLABEL_AUTHOR'] = 'Author';
$lang['en_US']['SiteTreeCMSWorkflow']['FIELDLABEL_PAGE'] = 'Page';
$lang['en_US']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHER'] = 'Publisher';
$lang['en_US']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHERS'] = 'Publishers';
$lang['en_US']['SiteTreeCMSWorkflow']['FIELDLABEL_STATUS'] = 'Status';
$lang['en_US']['SiteTreeCMSWorkflow']['NEXTREVIEWDATE'] = 'Next review date (leave blank for no review)';
$lang['en_US']['SiteTreeCMSWorkflow']['PAGEOWNER'] = 'Page owner (will be responsible for reviews)';
$lang['en_US']['SiteTreeCMSWorkflow']['APPROVEHEADER'] = 'Who can approve changes inside the CMS?';
$lang['en_US']['SiteTreeCMSWorkflow']['PUBLISHAPPROVEDHEADER'] = 'Who can publish approved requests inside the CMS?';
$lang['en_US']['SiteTreeCMSWorkflow']['PUBLISHHEADER'] = 'Who can publish this inside the CMS?';
$lang['en_US']['SiteTreeCMSWorkflow']['PUBLISHMESSAGE'] = 'Approved request and published changes to live version. Emailed %s.';
$lang['en_US']['SiteTreeCMSWorkflow']['REQUEST_DELETEFROMLIVE_SUCCESS_MESSAGE'] = 'Emailed %s requesting deletion';
$lang['en_US']['SiteTreeCMSWorkflow']['REQUEST_PUBLICATION_SUCCESS_MESSAGE'] = 'Emailed %s requesting publication';
$lang['en_US']['SiteTreeCMSWorkflow']['REVIEWFREQUENCY'] = 'Review frequency (the review date will be set to this far in the future whenever the page is published.)';
$lang['en_US']['SiteTreeCMSWorkflow']['REVIEWHEADER'] = 'Content review';
$lang['en_US']['SiteTreeCMSWorkflow']['STATUS_APPROVED'] = 'Approved';
$lang['en_US']['SiteTreeCMSWorkflow']['STATUS_AWAITINGAPPROVAL'] = 'Awaiting Approval';
$lang['en_US']['SiteTreeCMSWorkflow']['STATUS_AWAITINGEDIT'] = 'Awaiting Edit';
$lang['en_US']['SiteTreeCMSWorkflow']['STATUS_DENIED'] = 'Denied';
$lang['en_US']['SiteTreeCMSWorkflow']['STATUS_OPEN'] = 'Open';
$lang['en_US']['SiteTreeCMSWorkflow']['STATUS_UNKNOWN'] = 'Unknown';
$lang['en_US']['SiteTreeCMSWorkflow']['WORKFLOWACTION_APPROVE'] = 'Approve';
$lang['en_US']['SiteTreeCMSWorkflow']['WORKFLOWACTION_REQUESTEDIT'] = 'Request edit';
$lang['en_US']['SiteTreeCMSWorkflow']['WORKFLOW_ACTION_COMMENT'] = 'Comment';
$lang['en_US']['SiteTreeCMSWorkflow']['WORKFLOW_ACTION_DENY'] = 'Deny/cancel';
$lang['en_US']['SiteTreeCMSWorkflow']['WORKFLOW_ACTION_ACTION'] = 'Make it happen';
$lang['en_US']['SiteTreeCMSWorkflow']['WORKFLOW_ACTION_FAILED'] = 'There was an error when processing your workflow request.';
$lang['en_US']['SiteTreeCMSWorkflow']['WORKFLOW_ACTION_RESUBMIT'] = 'Re-submit';
$lang['en_US']['WorkflowDeletionRequest']['EMAIL_PARA_APPROVED'] = array(
	'%s has approved the request to delete the "%s" page and deleted it from the published site.',
	50,
	'Intro paragraph for workflow email, with a name and a page title'
);
$lang['en_US']['WorkflowDeletionRequest']['EMAIL_PARA_APPROVED_FOR_PUBLISHING'] = array(
	'%s has approved the request to delete the "%s" page. A user must now action your request.',
	50,
	'Intro paragraph for workflow email, with a name and a page title'
);
$lang['en_US']['WorkflowDeletionRequest']['EMAIL_PARA_AWAITINGAPPROVAL'] = array(
	'%s has asked that you delete the "%s" page',
	50,
	'Intro paragraph for workflow email, with a name and a page title'
);
$lang['en_US']['WorkflowDeletionRequest']['EMAIL_PARA_AWAITINGEDIT'] = array(
	'%s asked you to revise your request to delete the "%s" page.',
	50,
	'Intro paragraph for workflow email, with a name and a page title'
);
$lang['en_US']['WorkflowDeletionRequest']['EMAIL_PARA_COMMENT'] = array(
	'%s commented on the request to delete the "%s" page.',
	50,
	'Intro paragraph for workflow email, with a name and a page title'
);
$lang['en_US']['WorkflowDeletionRequest']['EMAIL_PARA_DENIED'] = array(
	'%s has rejected the request to delete the "%s" page.',
	50,
	'Intro paragraph for workflow email, with a name and a page title'
);
$lang['en_US']['WorkflowDeletionRequest']['EMAIL_SUBJECT_APPROVED'] = array(
	'Page deleted from published site: "%s"',
	50,
	'Email subject with page title'
);
$lang['en_US']['WorkflowDeletionRequest']['EMAIL_SUBJECT_APPROVED_FOR_PUBLISHING'] = array(
	'Page delete request from published site approved: "%s"',
	50,
	'Email subject with page title'
);
$lang['en_US']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = array(
	'Page deletion requested: %s',
	50,
	'Email subject with page title'
);
$lang['en_US']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = array(
	'Revision requested: "%s"',
	50,
	'Email subject with page title'
);
$lang['en_US']['WorkflowDeletionRequest']['EMAIL_SUBJECT_COMMENT'] = array(
	'Comment on deletion request: "%s"',
	50,
	'Email subject with page title'
);
$lang['en_US']['WorkflowDeletionRequest']['EMAIL_SUBJECT_DENIED'] = array(
	'Deletion rejected: "%s"',
	50,
	'Email subject with page title'
);
$lang['en_US']['WorkflowPublicationRequest']['EMAIL_PARA_APPROVED'] = array(
	'%s has approved and published the changes to the "%s" page.',
	50,
	'Intro paragraph for workflow email, with a name and a page title'
);
$lang['en_US']['WorkflowPublicationRequest']['EMAIL_PARA_APPROVED_FOR_PUBLISHING'] = array(
	'%s has approved the changes to the "%s" page. A user must now publish your request.',
	50,
	'Intro paragraph for workflow email, with a name and a page title'
);
$lang['en_US']['WorkflowPublicationRequest']['EMAIL_PARA_AWAITINGAPPROVAL'] = array(
	'%s has asked that you review and publish following change to the "%s" page',
	50,
	'Intro paragraph for workflow email, with a name and a page title'
);
$lang['en_US']['WorkflowPublicationRequest']['EMAIL_PARA_AWAITINGEDIT'] = array(
	'%s asked you to revise your changes to the "%s" page.',
	50,
	'Intro paragraph for workflow email, with a name and a page title'
);
$lang['en_US']['WorkflowPublicationRequest']['EMAIL_PARA_COMMENT'] = array(
	'%s commented on the requested change to the "%s" page.',
	50,
	'Intro paragraph for workflow email, with a name and a page title'
);
$lang['en_US']['WorkflowPublicationRequest']['EMAIL_PARA_DENIED'] = array(
	'%s has rejected the changes to the "%s" page.',
	50,
	'Intro paragraph for workflow email, with a name and a page title'
);
$lang['en_US']['WorkflowPublicationRequest']['EMAIL_SUBJECT_APPROVED'] = array(
	'Change published: "%s"',
	50,
	'Email subject with page title'
);
$lang['en_US']['WorkflowPublicationRequest']['EMAIL_SUBJECT_APPROVED_FOR_PUBLISHING'] = array(
	'Change approved for publishing: "%s"',
	50,
	'Email subject with page title'
);
$lang['en_US']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = array(
	'Publication of change requested: %s',
	50,
	'Email subject with page title'
);
$lang['en_US']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = array(
	'Revision requested: "%s"',
	50,
	'Email subject with page title'
);
$lang['en_US']['WorkflowPublicationRequest']['EMAIL_SUBJECT_COMMENT'] = array(
	'Comment on publication request: "%s"',
	50,
	'Email subject with page title'
);
$lang['en_US']['WorkflowPublicationRequest']['EMAIL_SUBJECT_DENIED'] = array(
	'Change rejected: "%s"',
	50,
	'Email subject with page title'
);
$lang['en_US']['WorkflowRequest']['CHANGES_HEADING'] = 'Changes';
$lang['en_US']['WorkflowRequest']['COMMENT_HEADING'] = 'Comment';
$lang['en_US']['WorkflowRequest']['EMAILGREETING'] = 'Hi %s';
$lang['en_US']['WorkflowRequest']['EMAILTHANKS'] = 'Thanks.';
$lang['en_US']['WorkflowRequest']['EMAIL_SUBJECT_GENERIC'] = array(
	'The workflow status of the "%s" page has changed',
	50,
	'Email subject with page title'
);
$lang['en_US']['WorkflowRequest']['REVIEWPAGELINK'] = 'Review the page in the CMS';
$lang['en_US']['WorkflowRequest']['TITLE'] = array(
	'Workflow Request',
	50,
	'Title for this request, shown e.g. in the workflow status overview for a page'
);
$lang['en_US']['WorkflowRequest']['VIEWPUBLISHEDLINK'] = 'View this page on your website';
$lang['en_US']['WorkflowRequestChange']['PLURALNAME'] = array(
	'Workflow Request Changs',
	50,
	'Pural name of the object, used in dropdowns and to generally identify a collection of this object in the interface'
);
$lang['en_US']['WorkflowRequestChange']['SINGULARNAME'] = array(
	'Workflow Request Change',
	50,
	'Singular name of the object, used in dropdowns and to generally identify a single object in the interface'
);

?>