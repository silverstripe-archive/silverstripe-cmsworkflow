<?php

/**
 * German (Germany) language pack
 * @package modules: cms workflow
 * @subpackage i18n
 */

i18n::include_locale_file('modules: cms workflow', 'en_US');

global $lang;

if(array_key_exists('de_DE', $lang) && is_array($lang['de_DE'])) {
	$lang['de_DE'] = array_merge($lang['en_US'], $lang['de_DE']);
} else {
	$lang['de_DE'] = $lang['en_US'];
}

$lang['de_DE']['DeletionRequestSideReport']['TITLE'] = 'Workflow: Erwarted Löschung';
$lang['de_DE']['MyWorkflowRequestsSideReport']['TITLE'] = 'Arbeitsablauf: Meine offenen Anfragen';
$lang['de_DE']['PublisherReviewSideReport']['TITLE'] = 'Worflow: Erwartet Veröffentlichung';
$lang['de_DE']['SiteTree']['EDITANYONE'] = 'Jeder, welcher sich im CMS Einloggen kann';
$lang['de_DE']['SiteTree']['EDITONLYTHESE'] = 'Nur folgende Personen (wähle aus der Liste)';
$lang['de_DE']['SiteTreeCMSWorkflow']['BUTTONDENYPUBLICATION'] = 'Veröffentlichung verweigern';
$lang['de_DE']['SiteTreeCMSWorkflow']['BUTTONREQUESTPUBLICATION'] = 'Veröffentlichung anfordern';
$lang['de_DE']['SiteTreeCMSWorkflow']['BUTTONREQUESTREMOVAL'] = 'Löschung anfordern';
$lang['de_DE']['SiteTreeCMSWorkflow']['CLOSEDREQUESTSHEADER'] = 'Geschlossene Anforderungen';
$lang['de_DE']['SiteTreeCMSWorkflow']['DENYDELECTIONMESSAGE'] = 'Die Anfrage wurde verweigert und die Seite auf die Live-Version zurückgesetzt. An %s gemailt';
$lang['de_DE']['SiteTreeCMSWorkflow']['DENYPUBLICATION_MESSAGE'] = 'Die Anfrage wurde verweigert und die Seite auf die Live-Version zurückgesetzt. An %s gemailt';
$lang['de_DE']['SiteTreeCMSWorkflow']['DIFFERENCESCOLUMN'] = 'Unterschiede';
$lang['de_DE']['SiteTreeCMSWorkflow']['DIFFERENCESLINK'] = 'Zeige die Unterschiede zur Live-Website';
$lang['de_DE']['SiteTreeCMSWorkflow']['DIFFERENCESTHISCHANGECOLUMN'] = 'Unterschiede in den Änderungen';
$lang['de_DE']['SiteTreeCMSWorkflow']['DIFFERENCESTOLIVECOLUMN'] = 'Unterschiede zur Live-Website';
$lang['de_DE']['SiteTreeCMSWorkflow']['FIELDLABEL_AUTHOR'] = 'Autor';
$lang['de_DE']['SiteTreeCMSWorkflow']['FIELDLABEL_PAGE'] = 'Seite';
$lang['de_DE']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHER'] = 'Herausgeber';
$lang['de_DE']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHERS'] = 'Herausgeber';
$lang['de_DE']['SiteTreeCMSWorkflow']['FIELDLABEL_STATUS'] = 'Status';
$lang['de_DE']['SiteTreeCMSWorkflow']['OPENREQUESTHEADER'] = 'Offene Anfragen';
$lang['de_DE']['SiteTreeCMSWorkflow']['OPENREQUESTSNOFOUND'] = 'Keine offene Anfragen gefunden';
$lang['de_DE']['SiteTreeCMSWorkflow']['PUBLISHHEADER'] = 'Wer kann dies im CMS veröffentlichen?';
$lang['de_DE']['SiteTreeCMSWorkflow']['REQUEST_DELETEFROMLIVE_SUCCESS_MESSAGE'] = 'Löschanfrage an %s gemailt';
$lang['de_DE']['SiteTreeCMSWorkflow']['REQUEST_PUBLICATION_SUCCESS_MESSAGE'] = 'Publikationsanfrage an %s gemailt';
$lang['de_DE']['SiteTreeCMSWorkflow']['STATUS_APPROVED'] = 'Bestätigt';
$lang['de_DE']['SiteTreeCMSWorkflow']['STATUS_AWAITINGAPPROVAL'] = 'Erwartet Bestätigung';
$lang['de_DE']['SiteTreeCMSWorkflow']['STATUS_AWAITINGEDIT'] = 'Erwartet Bearbeitung';
$lang['de_DE']['SiteTreeCMSWorkflow']['STATUS_DENIED'] = 'Verweigert';
$lang['de_DE']['SiteTreeCMSWorkflow']['STATUS_OPEN'] = 'Offen';
$lang['de_DE']['SiteTreeCMSWorkflow']['STATUS_UNKNOWN'] = 'Unbekannt';
$lang['de_DE']['SiteTreeCMSWorkflow']['WORKFLOWTABTITLE'] = 'Arbeitsablauf';
$lang['de_DE']['WorkflowDeletionRequest']['EMAIL_SUBJECT_APPROVED'] = 'Ihr Antrag zum Löschen der Seite "%s" wurde genehmigt';
$lang['de_DE']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Bitte überprüfen und löschen Sie die Seite "%s" auf Ihrer Website';
$lang['de_DE']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'Sie werden gebeten, die Seite "%s" zu überprüfen';
$lang['de_DE']['WorkflowDeletionRequest']['EMAIL_SUBJECT_DENIED'] = 'Ihr Antrag zum  Löschen der Seite "%s" wurde verweigert';
$lang['de_DE']['WorkflowPublicationRequest']['EMAIL_SUBJECT_APPROVED'] = 'Ihre Antrag zum Veröffentlichen der Seite "%s" wurde genehmigt';
$lang['de_DE']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Bitte überprüfen und veröffentlichen Sie die Seite "%s" auf Ihrer Website';
$lang['de_DE']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'Sie werden gebeten, die Seite "%s" zu prüfen';
$lang['de_DE']['WorkflowPublicationRequest']['EMAIL_SUBJECT_DENIED'] = 'Ihr Antrag zum Veröffentlichen der Seite "%s" wurde verweigert';
$lang['de_DE']['WorkflowRequest']['COMPAREDRAFTLIVELINK'] = 'Vergleiche Änderungen zwischen Live-Website und dem geändertem Entwurf';
$lang['de_DE']['WorkflowRequest']['EMAILCHANGEDSTATUS'] = '%s hat seinen Arbeitsablaufstatus auf';
$lang['de_DE']['WorkflowRequest']['EMAILDENIEDDELETION'] = '%s hat die Löschanfrage verweigert';
$lang['de_DE']['WorkflowRequest']['EMAILDENIEDPUBLICATION'] = '%s hat die Publikationsanfrage verweigert';
$lang['de_DE']['WorkflowRequest']['EMAILGREETING'] = 'Hallo %s';
$lang['de_DE']['WorkflowRequest']['EMAILHASBEENPUBLISHED'] = 'Eine von Ihnen verfasste Seite wurde nun von %v veröffentlicht';
$lang['de_DE']['WorkflowRequest']['EMAILRECENTLYUPDATED1'] = '%s hat kürzlich die genannte Seite verändert / upgedated';
$lang['de_DE']['WorkflowRequest']['EMAILRECENTLYUPDATED2'] = 'und möchte, dass die Änderungen veröffentlicht werden.';
$lang['de_DE']['WorkflowRequest']['EMAILREQUESTREMOVE'] = '%s möchte die genannte Seite löschen.';
$lang['de_DE']['WorkflowRequest']['EMAILTHANKS'] = 'Danke.';
$lang['de_DE']['WorkflowRequest']['EMAIL_SUBJECT_GENERIC'] = 'Der Bearbeitungsstand der Seite "%s" hat sich geändert';
$lang['de_DE']['WorkflowRequest']['REVIEWANDDELETEPAGELINK'] = 'Probelesen';
$lang['de_DE']['WorkflowRequest']['REVIEWANDPUBLISHPAGELINK'] = 'Prüfen und veröffentlichen der Seite im CMS';
$lang['de_DE']['WorkflowRequest']['REVIEWPAGELINK'] = 'Prüfen der Seite im CMS';
$lang['de_DE']['WorkflowRequest']['TITLE'] = 'Anfrage zur Bearbeitung';
$lang['de_DE']['WorkflowRequest']['VIEWCHANGEDDRAFTLINK'] = 'Ansicht des geänderten Entwurfs';
$lang['de_DE']['WorkflowRequest']['VIEWPUBLISHEDCHANGESLINK'] = 'Prüfen der veröffentlichten Änderungen im CMS';
$lang['de_DE']['WorkflowRequest']['VIEWPUBLISHEDLINK'] = 'Ansicht dieser Seite in deiner Webseite';
$lang['de_DE']['WorkflowRequest']['VIEWUNPUBLISHEDCHANGESLINK'] = 'Vergleiche unveröffentlichte Änderungen im CMS';
$lang['de_DE']['WorkflowRequestChange']['PLURALNAME'] = 'Geänderte Anfragen zur Bearbeitung';
$lang['de_DE']['WorkflowRequestChange']['SINGULARNAME'] = 'Geänderte Anfrage zur Bearbeitung';

?>