<?php

/**
 * Spanish (Mexico) language pack
 * @package cmsworkflow
 * @subpackage i18n
 */

i18n::include_locale_file('cmsworkflow', 'en_US');

global $lang;

if(array_key_exists('es_MX', $lang) && is_array($lang['es_MX'])) {
	$lang['es_MX'] = array_merge($lang['en_US'], $lang['es_MX']);
} else {
	$lang['es_MX'] = $lang['en_US'];
}

$lang['es_MX']['DeletionRequestSideReport']['TITLE'] = 'Flujo de Trabajo: Esperando eliminación';
$lang['es_MX']['MyWorkflowRequestsSideReport']['TITLE'] = 'Flujo de Trabajo: Mis peticiones pendientes por revisar';
$lang['es_MX']['PublisherReviewSideReport']['TITLE'] = 'Flujo de Trabajo: Esperando publicación';
$lang['es_MX']['SiteTree']['EDITANYONE'] = 'Cualquiera puede ingresar al CMS';
$lang['es_MX']['SiteTree']['EDITONLYTHESE'] = 'Únicamente estas personas (seleccionar de la lista)';
$lang['es_MX']['SiteTreeCMSWorkflow']['BUTTONDENYPUBLICATION'] = 'Denegar Publicación';
$lang['es_MX']['SiteTreeCMSWorkflow']['BUTTONREQUESTPUBLICATION'] = 'Solicitar Publicación';
$lang['es_MX']['SiteTreeCMSWorkflow']['BUTTONREQUESTREMOVAL'] = 'Solicitar Remoción';
$lang['es_MX']['SiteTreeCMSWorkflow']['CLOSEDREQUESTSHEADER'] = 'Peticiones Cerradas';
$lang['es_MX']['SiteTreeCMSWorkflow']['DENYDELECTIONMESSAGE'] = 'Denegar petición y reiniciar la página a la versión viva. Se enviará un correo electrónico a %s';
$lang['es_MX']['SiteTreeCMSWorkflow']['DENYPUBLICATION_MESSAGE'] = 'Denegar petición y reiniciar la página a la versión en vivo. Se enviará un correo electrónico a %s';
$lang['es_MX']['SiteTreeCMSWorkflow']['DIFFERENCESCOLUMN'] = 'Diferencias';
$lang['es_MX']['SiteTreeCMSWorkflow']['DIFFERENCESLINK'] = 'Muestra diferencias respecto a la versión en vivo';
$lang['es_MX']['SiteTreeCMSWorkflow']['DIFFERENCESTHISCHANGECOLUMN'] = 'Diferencias en este cambio';
$lang['es_MX']['SiteTreeCMSWorkflow']['DIFFERENCESTOLIVECOLUMN'] = 'Diferencias respecto a la versión en vivo';
$lang['es_MX']['SiteTreeCMSWorkflow']['FIELDLABEL_AUTHOR'] = 'Autor';
$lang['es_MX']['SiteTreeCMSWorkflow']['FIELDLABEL_PAGE'] = 'Página';
$lang['es_MX']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHER'] = 'Editor';
$lang['es_MX']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHERS'] = 'Editores';
$lang['es_MX']['SiteTreeCMSWorkflow']['FIELDLABEL_STATUS'] = 'Estado';
$lang['es_MX']['SiteTreeCMSWorkflow']['OPENREQUESTHEADER'] = 'Peticiones Abiertas';
$lang['es_MX']['SiteTreeCMSWorkflow']['OPENREQUESTSNOFOUND'] = 'No abrir petición encontrada';
$lang['es_MX']['SiteTreeCMSWorkflow']['PUBLISHHEADER'] = '¿Quién puede publicar dentro del CMS?';
$lang['es_MX']['SiteTreeCMSWorkflow']['REQUEST_DELETEFROMLIVE_SUCCESS_MESSAGE'] = 'Se envió por correo electrónico la petición para eliminar %s';
$lang['es_MX']['SiteTreeCMSWorkflow']['REQUEST_PUBLICATION_SUCCESS_MESSAGE'] = 'Se envió por correo electrónico la petición para publicar %s';
$lang['es_MX']['SiteTreeCMSWorkflow']['STATUS_APPROVED'] = 'Aprobado';
$lang['es_MX']['SiteTreeCMSWorkflow']['STATUS_AWAITINGAPPROVAL'] = 'Esperando Aprobación';
$lang['es_MX']['SiteTreeCMSWorkflow']['STATUS_AWAITINGEDIT'] = 'Esperando Modificación';
$lang['es_MX']['SiteTreeCMSWorkflow']['STATUS_DENIED'] = 'Denegada';
$lang['es_MX']['SiteTreeCMSWorkflow']['STATUS_OPEN'] = 'Abrir';
$lang['es_MX']['SiteTreeCMSWorkflow']['STATUS_UNKNOWN'] = 'Desconocido';
$lang['es_MX']['SiteTreeCMSWorkflow']['WORKFLOWTABTITLE'] = 'Flujo de trabajo';
$lang['es_MX']['WorkflowDeletionRequest']['EMAIL_SUBJECT_APPROVED'] = 'Tu petición para eliminar la página "%s" se tiene que aprobar';
$lang['es_MX']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Por favor revisa y elimina la página "%s" en tu sitio';
$lang['es_MX']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'Has solicitado revisar la página "%s"';
$lang['es_MX']['WorkflowDeletionRequest']['EMAIL_SUBJECT_DENIED'] = 'Se denegó tu petición para eliminar la página "%s"';
$lang['es_MX']['WorkflowPublicationRequest']['EMAIL_SUBJECT_APPROVED'] = 'Se aprobó tu solicitud para publicar la página "%s"';
$lang['es_MX']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Por favor revisa y publica la página "%s" en tu sitio';
$lang['es_MX']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'Estás solicitando un revisión de la página "%s"';
$lang['es_MX']['WorkflowPublicationRequest']['EMAIL_SUBJECT_DENIED'] = 'Se denegó tu solicitud para publicar la página "%s"';
$lang['es_MX']['WorkflowRequest']['COMPAREDRAFTLIVELINK'] = 'Compara cambios entre el vivo y el boceto cambiado';
$lang['es_MX']['WorkflowRequest']['EMAILCHANGEDSTATUS'] = 'ha cambiado el estado del flujo de trabajo en %s';
$lang['es_MX']['WorkflowRequest']['EMAILDENIEDDELETION'] = 'se denegó la petición para eliminar %s en';
$lang['es_MX']['WorkflowRequest']['EMAILDENIEDPUBLICATION'] = 'se ha denegado la solicitud para publicar %s en';
$lang['es_MX']['WorkflowRequest']['EMAILGREETING'] = 'Hola %s';
$lang['es_MX']['WorkflowRequest']['EMAILHASBEENPUBLISHED'] = 'La página que escribiste fue publicada por %s';
$lang['es_MX']['WorkflowRequest']['EMAILRECENTLYUPDATED1'] = 'recientemente se ha actualizado la página titulada %s';
$lang['es_MX']['WorkflowRequest']['EMAILRECENTLYUPDATED2'] = 'y desea publicar los cambios.';
$lang['es_MX']['WorkflowRequest']['EMAILREQUESTREMOVE'] = 'desea remover la página titulada %s';
$lang['es_MX']['WorkflowRequest']['EMAILTHANKS'] = 'Gracias.';
$lang['es_MX']['WorkflowRequest']['EMAIL_SUBJECT_GENERIC'] = 'Ha cambiado el estado del flujo de trabajo de la página "%s"';
$lang['es_MX']['WorkflowRequest']['REVIEWANDDELETEPAGELINK'] = 'Revisa y elimina la página en el CMS';
$lang['es_MX']['WorkflowRequest']['REVIEWANDPUBLISHPAGELINK'] = 'Revisa y publica la página en el CMS';
$lang['es_MX']['WorkflowRequest']['REVIEWPAGELINK'] = 'Revisa la página en el CMS';
$lang['es_MX']['WorkflowRequest']['TITLE'] = 'Solicitud de Flujo de Trabajo';
$lang['es_MX']['WorkflowRequest']['VIEWCHANGEDDRAFTLINK'] = 'Ve los cambios en el boceto';
$lang['es_MX']['WorkflowRequest']['VIEWPUBLISHEDCHANGESLINK'] = 'Revisa en el CMS los cambios publicados';
$lang['es_MX']['WorkflowRequest']['VIEWPUBLISHEDLINK'] = 'Ve esta página en tu sitio web';
$lang['es_MX']['WorkflowRequest']['VIEWUNPUBLISHEDCHANGESLINK'] = 'Comparar cambios sin publicar en el CMS';
$lang['es_MX']['WorkflowRequestChange']['PLURALNAME'] = 'Solicitud de Cambios en el Flujo de Trabajo';
$lang['es_MX']['WorkflowRequestChange']['SINGULARNAME'] = 'Solicitud de Cambios en el Flujo de Trabajo';

?>