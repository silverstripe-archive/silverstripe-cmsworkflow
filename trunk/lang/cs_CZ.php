<?php

/**
 * Czech (Czech Republic) language pack
 * @package cmsworkflow
 * @subpackage i18n
 */

i18n::include_locale_file('cmsworkflow', 'en_US');

global $lang;

if(array_key_exists('cs_CZ', $lang) && is_array($lang['cs_CZ'])) {
	$lang['cs_CZ'] = array_merge($lang['en_US'], $lang['cs_CZ']);
} else {
	$lang['cs_CZ'] = $lang['en_US'];
}

$lang['cs_CZ']['SiteTreeCMSWorkflow']['STATUS_APPROVED'] = 'Schváleno';
$lang['cs_CZ']['SiteTreeCMSWorkflow']['STATUS_AWAITINGAPPROVAL'] = 'Čeká na schválení';
$lang['cs_CZ']['SiteTreeCMSWorkflow']['STATUS_AWAITINGEDIT'] = 'Čeká na editaci';
$lang['cs_CZ']['SiteTreeCMSWorkflow']['STATUS_DENIED'] = 'Zamítnuto';
$lang['cs_CZ']['SiteTreeCMSWorkflow']['STATUS_OPEN'] = 'Otevřít';
$lang['cs_CZ']['SiteTreeCMSWorkflow']['STATUS_UNKNOWN'] = 'Neznámé';
$lang['cs_CZ']['WorkflowDeletionRequest']['EMAIL_SUBJECT_APPROVED'] = 'Vaše žádosti o zrušení "% s" stránky byla schválena';
$lang['cs_CZ']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Zkontrolujte a smažte stránku "%s" na svém webu, prosím.';
$lang['cs_CZ']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'Žádost o zkontrolování stránky "%s"';
$lang['cs_CZ']['WorkflowDeletionRequest']['EMAIL_SUBJECT_DENIED'] = 'Váš požadavek na "%s" stránku byl zamítnut';
$lang['cs_CZ']['WorkflowPublicationRequest']['EMAIL_SUBJECT_APPROVED'] = 'Vaše žádost o publikování stránky "%s" byla schválena';
$lang['cs_CZ']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Prosím zkontrolujte a publikujte "%s" stránku na svém webu';
$lang['cs_CZ']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'Jste dotazován na zkontrolování stránky  "%s"';
$lang['cs_CZ']['WorkflowPublicationRequest']['EMAIL_SUBJECT_DENIED'] = 'Vaše žádost o publikování stránky "%s" byla zamítnuta';
$lang['cs_CZ']['WorkflowRequest']['EMAILDENIEDDELETION'] = '%s zamítnul požadavek k odstranění';
$lang['cs_CZ']['WorkflowRequest']['EMAILDENIEDPUBLICATION'] = '%s zamítnul váš požadavek k publikaci změn v';
$lang['cs_CZ']['WorkflowRequest']['EMAILGREETING'] = 'Ahoj %s';
$lang['cs_CZ']['WorkflowRequest']['EMAILHASBEENPUBLISHED'] = 'Stránka, kterou jste napsal byla právě publikována uživatelem %s';
$lang['cs_CZ']['WorkflowRequest']['EMAILRECENTLYUPDATED1'] = '%s právě aktualizoval stránku';
$lang['cs_CZ']['WorkflowRequest']['EMAILRECENTLYUPDATED2'] = 'a chtěl by publikovat změny.';
$lang['cs_CZ']['WorkflowRequest']['EMAILREQUESTREMOVE'] = '%s chce odstranit stránku';
$lang['cs_CZ']['WorkflowRequest']['EMAILTHANKS'] = 'Děkuji.';
$lang['cs_CZ']['WorkflowRequest']['EMAIL_SUBJECT_GENERIC'] = 'Status stránky "%s" se změnil';
$lang['cs_CZ']['WorkflowRequest']['REVIEWANDDELETEPAGELINK'] = 'Zrevidovat a smazat stránku CMS';
$lang['cs_CZ']['WorkflowRequest']['REVIEWANDPUBLISHPAGELINK'] = 'Zrevidovat a publikovat stránku CMS';
$lang['cs_CZ']['WorkflowRequest']['REVIEWPAGELINK'] = 'Zkontrolovat stránku v CMS';
$lang['cs_CZ']['WorkflowRequest']['VIEWCHANGEDDRAFTLINK'] = 'Zobrzit změněný návrh';
$lang['cs_CZ']['WorkflowRequest']['VIEWPUBLISHEDCHANGESLINK'] = 'Zrevidovat publikované změny v CMS';
$lang['cs_CZ']['WorkflowRequest']['VIEWPUBLISHEDLINK'] = 'Náhled této stránky na webu';
$lang['cs_CZ']['WorkflowRequest']['VIEWUNPUBLISHEDCHANGESLINK'] = 'Porovnat nepublikované změny v CMS';

?>