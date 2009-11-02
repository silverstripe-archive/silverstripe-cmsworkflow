<?php

/**
 * Estonian (Estonia) language pack
 * @package modules: cms workflow
 * @subpackage i18n
 */

i18n::include_locale_file('modules: cms workflow', 'en_US');

global $lang;

if(array_key_exists('et_EE', $lang) && is_array($lang['et_EE'])) {
	$lang['et_EE'] = array_merge($lang['en_US'], $lang['et_EE']);
} else {
	$lang['et_EE'] = $lang['en_US'];
}

$lang['et_EE']['DeletionRequestSideReport']['TITLE'] = 'Tööde voog: Ootab kustutamist';
$lang['et_EE']['MyWorkflowRequestsSideReport']['TITLE'] = 'Tööde voog: Minu soov ootab ülevaatamaist';
$lang['et_EE']['PublisherReviewSideReport']['TITLE'] = 'Tööde voog: Ootab avaldamist';
$lang['et_EE']['SiteTree']['EDITANYONE'] = 'Üks kõik kellel on õigus sisuhaldusele ligipääseda';
$lang['et_EE']['SiteTree']['EDITONLYTHESE'] = 'Ainult need isikud(vali nimekirjast)';
$lang['et_EE']['SiteTreeCMSWorkflow']['BUTTONDENYPUBLICATION'] = 'Soovist keeldumine';
$lang['et_EE']['SiteTreeCMSWorkflow']['BUTTONREQUESTPUBLICATION'] = 'Soovi avaldamine';
$lang['et_EE']['SiteTreeCMSWorkflow']['BUTTONREQUESTREMOVAL'] = 'Soovi eemaldamine';
$lang['et_EE']['SiteTreeCMSWorkflow']['CLOSEDREQUESTSHEADER'] = 'Suletud soovid';
$lang['et_EE']['SiteTreeCMSWorkflow']['DENYDELECTIONMESSAGE'] = 'Keelduti soovist ja tühistati leht algseks versiooniks. emailiti %s d';
$lang['et_EE']['SiteTreeCMSWorkflow']['DENYPUBLICATION_MESSAGE'] = 'Keelduti soovist ja tühistati leht algseks versiooniks. emailiti %s d';
$lang['et_EE']['SiteTreeCMSWorkflow']['DIFFERENCESCOLUMN'] = 'Erinevused';
$lang['et_EE']['SiteTreeCMSWorkflow']['DIFFERENCESLINK'] = 'näita avaldatud muudatusi';
$lang['et_EE']['SiteTreeCMSWorkflow']['DIFFERENCESTHISCHANGECOLUMN'] = 'erinevused muudatustega';
$lang['et_EE']['SiteTreeCMSWorkflow']['DIFFERENCESTOLIVECOLUMN'] = 'erinevused avaldatuga';
$lang['et_EE']['SiteTreeCMSWorkflow']['FIELDLABEL_AUTHOR'] = 'Autor';
$lang['et_EE']['SiteTreeCMSWorkflow']['FIELDLABEL_PAGE'] = 'Leht';
$lang['et_EE']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHER'] = 'Kirjastaja';
$lang['et_EE']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHERS'] = 'Kirjastajad';
$lang['et_EE']['SiteTreeCMSWorkflow']['FIELDLABEL_STATUS'] = 'Staatus';
$lang['et_EE']['SiteTreeCMSWorkflow']['OPENREQUESTHEADER'] = 'Avaldatud soovid';
$lang['et_EE']['SiteTreeCMSWorkflow']['OPENREQUESTSNOFOUND'] = 'avaldatud soove ei leitud';
$lang['et_EE']['SiteTreeCMSWorkflow']['PUBLISHHEADER'] = 'Kes saavad avaldada sisuhalduses?';
$lang['et_EE']['SiteTreeCMSWorkflow']['REQUEST_DELETEFROMLIVE_SUCCESS_MESSAGE'] = 'mailis %s kustutamis soovi';
$lang['et_EE']['SiteTreeCMSWorkflow']['REQUEST_PUBLICATION_SUCCESS_MESSAGE'] = 'mailis %s avaldamis soovi';
$lang['et_EE']['SiteTreeCMSWorkflow']['STATUS_APPROVED'] = 'Kinnitatud';
$lang['et_EE']['SiteTreeCMSWorkflow']['STATUS_AWAITINGAPPROVAL'] = 'Kinnitamise ootel';
$lang['et_EE']['SiteTreeCMSWorkflow']['STATUS_AWAITINGEDIT'] = 'Muudatuste ootel';
$lang['et_EE']['SiteTreeCMSWorkflow']['STATUS_DENIED'] = 'Keelatud';
$lang['et_EE']['SiteTreeCMSWorkflow']['STATUS_OPEN'] = 'Avatud';
$lang['et_EE']['SiteTreeCMSWorkflow']['STATUS_UNKNOWN'] = 'Teadmata';
$lang['et_EE']['SiteTreeCMSWorkflow']['WORKFLOWTABTITLE'] = 'Töövoog';
$lang['et_EE']['WorkflowDeletionRequest']['EMAIL_SUBJECT_APPROVED'] = 'Kustutamine  "%s" lehele on kinnitatud';
$lang['et_EE']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Palun kontrolli ja kustuta "%s" leht oma saidilt';
$lang['et_EE']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'Teile on esitaud ülevaatamis soov  "%s" lehele';
$lang['et_EE']['WorkflowDeletionRequest']['EMAIL_SUBJECT_DENIED'] = 'Teie soov kustutada "%s" leht. lükkati tagasi';
$lang['et_EE']['WorkflowPublicationRequest']['EMAIL_SUBJECT_APPROVED'] = 'Teie "%s" lehe avaldamine. sai loa';
$lang['et_EE']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Palun kontrolli ja avalda "%s" leht oma saidil';
$lang['et_EE']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'Teile esitati soov "%s" lehe kontrollimiseks';
$lang['et_EE']['WorkflowPublicationRequest']['EMAIL_SUBJECT_DENIED'] = 'Teie avaldmais soov lehele "%s". Lükati tagasi';
$lang['et_EE']['WorkflowRequest']['COMPAREDRAFTLIVELINK'] = 'Võrdle muudatusi avaldatud lehel ja mustandis';
$lang['et_EE']['WorkflowRequest']['EMAILCHANGEDSTATUS'] = '%s muutis teie töövoo staatust';
$lang['et_EE']['WorkflowRequest']['EMAILDENIEDDELETION'] = '%s keeldus teie kustutamise soovist';
$lang['et_EE']['WorkflowRequest']['EMAILDENIEDPUBLICATION'] = '%s keeldus teie avaldamise soovist';
$lang['et_EE']['WorkflowRequest']['EMAILGREETING'] = 'Tere %s';
$lang['et_EE']['WorkflowRequest']['EMAILHASBEENPUBLISHED'] = 'Leht mille kirjutasid on nüüd avaldatud %s poolt';
$lang['et_EE']['WorkflowRequest']['EMAILRECENTLYUPDATED1'] = '%s muutis äsja lehte nimega';
$lang['et_EE']['WorkflowRequest']['EMAILRECENTLYUPDATED2'] = 'ja soovib muudatusi avaldada';
$lang['et_EE']['WorkflowRequest']['EMAILREQUESTREMOVE'] = '%s soovib eemaldada lehte nimega';
$lang['et_EE']['WorkflowRequest']['EMAILTHANKS'] = 'Aitäh.';
$lang['et_EE']['WorkflowRequest']['EMAIL_SUBJECT_GENERIC'] = 'Töödevoo staatus "%s" lehele on muudetud';
$lang['et_EE']['WorkflowRequest']['REVIEWANDDELETEPAGELINK'] = 'Komenteeri ja kustuta leht sisuhaldusest';
$lang['et_EE']['WorkflowRequest']['REVIEWANDPUBLISHPAGELINK'] = 'Komenteeri ja avalda leht CMS-is';
$lang['et_EE']['WorkflowRequest']['REVIEWPAGELINK'] = 'Komenteeri lehte CMS-is';
$lang['et_EE']['WorkflowRequest']['TITLE'] = 'Töödevoo sooviavaldus';
$lang['et_EE']['WorkflowRequest']['VIEWCHANGEDDRAFTLINK'] = 'Vaata muudetud mustandit';
$lang['et_EE']['WorkflowRequest']['VIEWPUBLISHEDCHANGESLINK'] = 'Komenteeri avaldatud muudatusi CMS-is';
$lang['et_EE']['WorkflowRequest']['VIEWPUBLISHEDLINK'] = 'Vaata seda lehte oma saidil';
$lang['et_EE']['WorkflowRequest']['VIEWUNPUBLISHEDCHANGESLINK'] = 'Võrdle avaldamata muudatusi CMS-is';
$lang['et_EE']['WorkflowRequestChange']['PLURALNAME'] = 'Töödevoo sooviavalduse muudatused';
$lang['et_EE']['WorkflowRequestChange']['SINGULARNAME'] = 'Töödevoo soovialavduste muudatus';

?>