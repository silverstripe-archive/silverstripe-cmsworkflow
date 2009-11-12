<?php

/**
 * Norwegian Bokmal (Norway) language pack
 * @package cmsworkflow
 * @subpackage i18n
 */

i18n::include_locale_file('cmsworkflow', 'en_US');

global $lang;

if(array_key_exists('nb_NO', $lang) && is_array($lang['nb_NO'])) {
	$lang['nb_NO'] = array_merge($lang['en_US'], $lang['nb_NO']);
} else {
	$lang['nb_NO'] = $lang['en_US'];
}

$lang['nb_NO']['DeletionRequestSideReport']['TITLE'] = 'Arbeidsflyt: Venter på sletting';
$lang['nb_NO']['MyWorkflowRequestsSideReport']['TITLE'] = 'Arbeidsflyt: Mine forespørsler under gjennomgåelse';
$lang['nb_NO']['PublisherReviewSideReport']['TITLE'] = 'Arbeidsflyt: Venter på publisering';
$lang['nb_NO']['SiteTree']['EDITANYONE'] = 'Alle kan logge på CMS\'en';
$lang['nb_NO']['SiteTree']['EDITONLYTHESE'] = 'Kun disse folkene (velg fra listen)';
$lang['nb_NO']['SiteTreeCMSWorkflow']['BUTTONDENYPUBLICATION'] = 'Nekt publisering';
$lang['nb_NO']['SiteTreeCMSWorkflow']['BUTTONREQUESTPUBLICATION'] = 'Forespør publisering';
$lang['nb_NO']['SiteTreeCMSWorkflow']['BUTTONREQUESTREMOVAL'] = 'Forespør fjerning';
$lang['nb_NO']['SiteTreeCMSWorkflow']['CLOSEDREQUESTSHEADER'] = 'Lukket forespørsel';
$lang['nb_NO']['SiteTreeCMSWorkflow']['DENYDELECTIONMESSAGE'] = 'Avvis forespørsel og gjenopprett side til publisert versjon. Epost %er';
$lang['nb_NO']['SiteTreeCMSWorkflow']['DENYPUBLICATION_MESSAGE'] = 'Avvis forespørsel og gjenopprett side til publisert versjon. Epost %er';
$lang['nb_NO']['SiteTreeCMSWorkflow']['DIFFERENCESCOLUMN'] = 'Ulikheter';
$lang['nb_NO']['SiteTreeCMSWorkflow']['DIFFERENCESLINK'] = 'Vis ulikhetene live';
$lang['nb_NO']['SiteTreeCMSWorkflow']['DIFFERENCESTHISCHANGECOLUMN'] = 'Ulikheter i denne endringen';
$lang['nb_NO']['SiteTreeCMSWorkflow']['DIFFERENCESTOLIVECOLUMN'] = 'Ulikheter med publisert';
$lang['nb_NO']['SiteTreeCMSWorkflow']['FIELDLABEL_AUTHOR'] = 'Forfatter';
$lang['nb_NO']['SiteTreeCMSWorkflow']['FIELDLABEL_PAGE'] = 'Side';
$lang['nb_NO']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHER'] = 'Utgiver';
$lang['nb_NO']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHERS'] = 'Utgivere';
$lang['nb_NO']['SiteTreeCMSWorkflow']['FIELDLABEL_STATUS'] = 'Status';
$lang['nb_NO']['SiteTreeCMSWorkflow']['OPENREQUESTHEADER'] = 'Åpen forespørsel';
$lang['nb_NO']['SiteTreeCMSWorkflow']['OPENREQUESTSNOFOUND'] = 'Ingen åpen forespørsel funnet';
$lang['nb_NO']['SiteTreeCMSWorkflow']['PUBLISHHEADER'] = 'Hvem kan publisere dette inni CMS\'en?';
$lang['nb_NO']['SiteTreeCMSWorkflow']['REQUEST_DELETEFROMLIVE_SUCCESS_MESSAGE'] = 'Epost %er forespør sletting';
$lang['nb_NO']['SiteTreeCMSWorkflow']['REQUEST_PUBLICATION_SUCCESS_MESSAGE'] = 'Eposter %s forespør publisering';
$lang['nb_NO']['SiteTreeCMSWorkflow']['STATUS_APPROVED'] = 'Godkjent';
$lang['nb_NO']['SiteTreeCMSWorkflow']['STATUS_AWAITINGAPPROVAL'] = 'Venter på godkjenning';
$lang['nb_NO']['SiteTreeCMSWorkflow']['STATUS_AWAITINGEDIT'] = 'Venter på endring';
$lang['nb_NO']['SiteTreeCMSWorkflow']['STATUS_DENIED'] = 'Avvist';
$lang['nb_NO']['SiteTreeCMSWorkflow']['STATUS_OPEN'] = 'Åpen';
$lang['nb_NO']['SiteTreeCMSWorkflow']['STATUS_UNKNOWN'] = 'Ukjent';
$lang['nb_NO']['SiteTreeCMSWorkflow']['WORKFLOWTABTITLE'] = 'Arbeidsflyt';
$lang['nb_NO']['WorkflowDeletionRequest']['EMAIL_SUBJECT_APPROVED'] = 'Din forespørsel om å slette "%er" sider er godtatt';
$lang['nb_NO']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Vennligst gå igjennom og slett "%er" side på ditte nettsted';
$lang['nb_NO']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'Du er anbefalt å se over "%er" side';
$lang['nb_NO']['WorkflowDeletionRequest']['EMAIL_SUBJECT_DENIED'] = 'Din forespørsel om å slette "%er" side har blitt avvist';
$lang['nb_NO']['WorkflowPublicationRequest']['EMAIL_SUBJECT_APPROVED'] = 'Din forespørsel om å publisere "%er" har blitt godkjent';
$lang['nb_NO']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Vennligst se over og publiser "%er" side på ditt nettsted';
$lang['nb_NO']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'Du er spurt om å se igjennom "%er" side';
$lang['nb_NO']['WorkflowPublicationRequest']['EMAIL_SUBJECT_DENIED'] = 'Din forespørsel om å publisere "%er" har blitt avvist';
$lang['nb_NO']['WorkflowRequest']['COMPAREDRAFTLIVELINK'] = 'Sammenlign endringer mellom publisert og endre utkast';
$lang['nb_NO']['WorkflowRequest']['EMAILCHANGEDSTATUS'] = '%s har endret arbeidsflyt status på';
$lang['nb_NO']['WorkflowRequest']['EMAILDENIEDDELETION'] = '%s har avvist din sletteforespørsel på';
$lang['nb_NO']['WorkflowRequest']['EMAILDENIEDPUBLICATION'] = '%s har avvist din publiseringsforspørsel på';
$lang['nb_NO']['WorkflowRequest']['EMAILGREETING'] = 'Hei %s';
$lang['nb_NO']['WorkflowRequest']['EMAILHASBEENPUBLISHED'] = 'En side du skrev har nå blitt publisert av %s';
$lang['nb_NO']['WorkflowRequest']['EMAILRECENTLYUPDATED1'] = '%s har nylig oppdatert siden med tittel';
$lang['nb_NO']['WorkflowRequest']['EMAILRECENTLYUPDATED2'] = 'og ønsker å ha endringer publisert.';
$lang['nb_NO']['WorkflowRequest']['EMAILREQUESTREMOVE'] = '%s ønsker å fjerne siden med tittel';
$lang['nb_NO']['WorkflowRequest']['EMAILTHANKS'] = 'Takk.';
$lang['nb_NO']['WorkflowRequest']['EMAIL_SUBJECT_GENERIC'] = 'Statusen til arbeidsflyten til "%er" sider er endret';
$lang['nb_NO']['WorkflowRequest']['REVIEWANDDELETEPAGELINK'] = 'Se igjennom og slett siden i CMS\'en';
$lang['nb_NO']['WorkflowRequest']['REVIEWANDPUBLISHPAGELINK'] = 'Se igjennom og publiser siden i CMS\'en';
$lang['nb_NO']['WorkflowRequest']['REVIEWPAGELINK'] = 'Se igjennom siden i CMS\'en';
$lang['nb_NO']['WorkflowRequest']['TITLE'] = 'Arbeidsflyt Forespørsel';
$lang['nb_NO']['WorkflowRequest']['VIEWCHANGEDDRAFTLINK'] = 'Vis endret utkast';
$lang['nb_NO']['WorkflowRequest']['VIEWPUBLISHEDCHANGESLINK'] = 'Gå igjennom publiserte endringer i CMS\'en';
$lang['nb_NO']['WorkflowRequest']['VIEWPUBLISHEDLINK'] = 'Vis denne siden på ditt nettsted';
$lang['nb_NO']['WorkflowRequest']['VIEWUNPUBLISHEDCHANGESLINK'] = 'Sammenlign upubliserte endringer i CMS';
$lang['nb_NO']['WorkflowRequestChange']['PLURALNAME'] = 'Arbeidsflyt forespørsel endret';
$lang['nb_NO']['WorkflowRequestChange']['SINGULARNAME'] = 'Arbeidsflytforespørsel endres';

?>