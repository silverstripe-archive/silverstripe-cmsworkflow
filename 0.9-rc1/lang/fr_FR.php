<?php

/**
 * French (France) language pack
 * @package modules: cms workflow
 * @subpackage i18n
 */

i18n::include_locale_file('modules: cms workflow', 'en_US');

global $lang;

if(array_key_exists('fr_FR', $lang) && is_array($lang['fr_FR'])) {
	$lang['fr_FR'] = array_merge($lang['en_US'], $lang['fr_FR']);
} else {
	$lang['fr_FR'] = $lang['en_US'];
}

$lang['fr_FR']['DeletionRequestSideReport']['TITLE'] = 'Edition : En Attente de suppression';
$lang['fr_FR']['MyWorkflowRequestsSideReport']['TITLE'] = 'Edition : Mes requêtes attendent d\'être revues';
$lang['fr_FR']['PublisherReviewSideReport']['TITLE'] = 'Edition : En attente de publication';
$lang['fr_FR']['SiteTree']['EDITANYONE'] = 'Tous ceux qui peuvent se connecter au CMS';
$lang['fr_FR']['SiteTree']['EDITONLYTHESE'] = 'Seulement ces personnes (choisir dans la liste)';
$lang['fr_FR']['SiteTreeCMSWorkflow']['BUTTONDENYPUBLICATION'] = 'Refuser la Publication';
$lang['fr_FR']['SiteTreeCMSWorkflow']['BUTTONREQUESTPUBLICATION'] = 'Demander la Publication';
$lang['fr_FR']['SiteTreeCMSWorkflow']['BUTTONREQUESTREMOVAL'] = 'Demander la Suppression';
$lang['fr_FR']['SiteTreeCMSWorkflow']['CLOSEDREQUESTSHEADER'] = 'Requêtes terminées';
$lang['fr_FR']['SiteTreeCMSWorkflow']['DIFFERENCESCOLUMN'] = 'Différences';
$lang['fr_FR']['SiteTreeCMSWorkflow']['DIFFERENCESLINK'] = 'Publier les modifications';
$lang['fr_FR']['SiteTreeCMSWorkflow']['DIFFERENCESTHISCHANGECOLUMN'] = 'Différences dans cette modification';
$lang['fr_FR']['SiteTreeCMSWorkflow']['DIFFERENCESTOLIVECOLUMN'] = 'Publication des modifications';
$lang['fr_FR']['SiteTreeCMSWorkflow']['FIELDLABEL_AUTHOR'] = 'Auteur';
$lang['fr_FR']['SiteTreeCMSWorkflow']['FIELDLABEL_PAGE'] = 'Page';
$lang['fr_FR']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHER'] = 'Editeur';
$lang['fr_FR']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHERS'] = 'Editeurs';
$lang['fr_FR']['SiteTreeCMSWorkflow']['FIELDLABEL_STATUS'] = 'Statut';
$lang['fr_FR']['SiteTreeCMSWorkflow']['OPENREQUESTHEADER'] = 'Requêtes en cours';
$lang['fr_FR']['SiteTreeCMSWorkflow']['OPENREQUESTSNOFOUND'] = 'Aucune requête en cours';
$lang['fr_FR']['SiteTreeCMSWorkflow']['PUBLISHHEADER'] = 'Qui peut publier ceci dans le CMS ?';
$lang['fr_FR']['SiteTreeCMSWorkflow']['REQUEST_DELETEFROMLIVE_SUCCESS_MESSAGE'] = 'Requête de suppression envoyée par email à %s';
$lang['fr_FR']['SiteTreeCMSWorkflow']['STATUS_APPROVED'] = 'Approuvé';
$lang['fr_FR']['SiteTreeCMSWorkflow']['STATUS_AWAITINGAPPROVAL'] = 'En attente d\'Approbation';
$lang['fr_FR']['SiteTreeCMSWorkflow']['STATUS_AWAITINGEDIT'] = 'En attente d\'Edition';
$lang['fr_FR']['SiteTreeCMSWorkflow']['STATUS_DENIED'] = 'Refusé';
$lang['fr_FR']['SiteTreeCMSWorkflow']['STATUS_OPEN'] = 'En cours';
$lang['fr_FR']['SiteTreeCMSWorkflow']['STATUS_UNKNOWN'] = 'Inconnu';
$lang['fr_FR']['SiteTreeCMSWorkflow']['WORKFLOWTABTITLE'] = 'Edition';
$lang['fr_FR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_APPROVED'] = 'Votre requête de suppression de la page "%s" a été approuvée';
$lang['fr_FR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Veuillez revoir et supprimer la page "%s" de votre site';
$lang['fr_FR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'Il vous est demandé de revoir la page "%s"';
$lang['fr_FR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_DENIED'] = 'Votre requête de suppression de la page "%s" a été refusée';
$lang['fr_FR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_APPROVED'] = 'Votre requête de publication de la page "%s" a été approuvée';
$lang['fr_FR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Veuillez revoir et publier la page "%s" de votre site';
$lang['fr_FR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'Vous êtes invité à revoir la page "%s"';
$lang['fr_FR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_DENIED'] = 'Votre requête de publication de la page "%s" a été refusée';
$lang['fr_FR']['WorkflowRequest']['COMPAREDRAFTLIVELINK'] = 'Comparer les modifications entre la version publiée et le brouillon modifié';
$lang['fr_FR']['WorkflowRequest']['EMAILCHANGEDSTATUS'] = '%s a changé le statut d\'édition le';
$lang['fr_FR']['WorkflowRequest']['EMAILDENIEDDELETION'] = '%s a refusé votre requête de suppression le';
$lang['fr_FR']['WorkflowRequest']['EMAILDENIEDPUBLICATION'] = '%s a refusé votre requête de publication le';
$lang['fr_FR']['WorkflowRequest']['EMAILGREETING'] = 'Salut %s';
$lang['fr_FR']['WorkflowRequest']['EMAILHASBEENPUBLISHED'] = 'Une page que vous avez écrit a été publiée par %s';
$lang['fr_FR']['WorkflowRequest']['EMAILRECENTLYUPDATED1'] = '%s a récemment mis à jour la page intitulée';
$lang['fr_FR']['WorkflowRequest']['EMAILRECENTLYUPDATED2'] = 'et voudrait que les modifications soient publiées';
$lang['fr_FR']['WorkflowRequest']['EMAILREQUESTREMOVE'] = '%s veut que vous supprimiez la page intitulée';
$lang['fr_FR']['WorkflowRequest']['EMAILTHANKS'] = 'Merci.';
$lang['fr_FR']['WorkflowRequest']['EMAIL_SUBJECT_GENERIC'] = 'Le statut d\'édition de la page "%s" a changé';
$lang['fr_FR']['WorkflowRequest']['REVIEWANDDELETEPAGELINK'] = 'Revoir et supprimer la page dans le CMS';
$lang['fr_FR']['WorkflowRequest']['REVIEWANDPUBLISHPAGELINK'] = 'Revoir et publier la page dans le CMS';
$lang['fr_FR']['WorkflowRequest']['REVIEWPAGELINK'] = 'Revoir la page dans le CMS';
$lang['fr_FR']['WorkflowRequest']['TITLE'] = 'Requête d\'Edition';
$lang['fr_FR']['WorkflowRequest']['VIEWCHANGEDDRAFTLINK'] = 'Voir le brouillon modifié';
$lang['fr_FR']['WorkflowRequest']['VIEWPUBLISHEDCHANGESLINK'] = 'Revoir les modifications publiées dans le CMS';
$lang['fr_FR']['WorkflowRequest']['VIEWPUBLISHEDLINK'] = 'Voir cette page sur votre site Web';
$lang['fr_FR']['WorkflowRequest']['VIEWUNPUBLISHEDCHANGESLINK'] = 'Comparer les modifications non publiées dans le CMS';

?>