<?php

/**
 * Spanish (Argentina) language pack
 * @package modules: cms workflow
 * @subpackage i18n
 */

i18n::include_locale_file('modules: cms workflow', 'en_US');

global $lang;

if(array_key_exists('es_AR', $lang) && is_array($lang['es_AR'])) {
	$lang['es_AR'] = array_merge($lang['en_US'], $lang['es_AR']);
} else {
	$lang['es_AR'] = $lang['en_US'];
}

$lang['es_AR']['DeletionRequestSideReport']['TITLE'] = 'Flujo de Trabajo: Esperando su eliminación';
$lang['es_AR']['MyWorkflowRequestsSideReport']['TITLE'] = 'Flujo de Trabajo: Mis requerimientos de revista en espera';
$lang['es_AR']['PublisherReviewSideReport']['TITLE'] = 'Flujo de Trabajo: Esperando publicación';
$lang['es_AR']['SiteTree']['EDITANYONE'] = 'Cualquiera que pueda ingresar al CMS';
$lang['es_AR']['SiteTree']['EDITONLYTHESE'] = 'Solo éstas personas (elegir de la lista)';
$lang['es_AR']['SiteTreeCMSWorkflow']['BUTTONDENYPUBLICATION'] = 'Negar Publicación';
$lang['es_AR']['SiteTreeCMSWorkflow']['BUTTONREQUESTPUBLICATION'] = 'Requerir Publicación';
$lang['es_AR']['SiteTreeCMSWorkflow']['BUTTONREQUESTREMOVAL'] = 'Requerir su Retiro';
$lang['es_AR']['SiteTreeCMSWorkflow']['CLOSEDREQUESTSHEADER'] = 'Requerimientos Cerrados';
$lang['es_AR']['SiteTreeCMSWorkflow']['DENYDELECTIONMESSAGE'] = 'Requerimiento denegado y reseteo de página a versión viva. Se envío email a %s';
$lang['es_AR']['SiteTreeCMSWorkflow']['DENYPUBLICATION_MESSAGE'] = 'Requerimiento denegado y reseteo de página a versión viva. Se envío email a %s';
$lang['es_AR']['SiteTreeCMSWorkflow']['DIFFERENCESCOLUMN'] = 'Diferencias';
$lang['es_AR']['SiteTreeCMSWorkflow']['DIFFERENCESLINK'] = 'Mostrar diferencias a vivir';
$lang['es_AR']['SiteTreeCMSWorkflow']['DIFFERENCESTHISCHANGECOLUMN'] = 'Diferencias en este cambio';
$lang['es_AR']['SiteTreeCMSWorkflow']['DIFFERENCESTOLIVECOLUMN'] = 'Diferencias para vivir';
$lang['es_AR']['SiteTreeCMSWorkflow']['FIELDLABEL_AUTHOR'] = 'Autor';
$lang['es_AR']['SiteTreeCMSWorkflow']['FIELDLABEL_PAGE'] = 'Página';
$lang['es_AR']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHER'] = 'Editore';
$lang['es_AR']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHERS'] = 'Editores';
$lang['es_AR']['SiteTreeCMSWorkflow']['FIELDLABEL_STATUS'] = 'Estado';
$lang['es_AR']['SiteTreeCMSWorkflow']['OPENREQUESTHEADER'] = 'Requerimiento Abierto';
$lang['es_AR']['SiteTreeCMSWorkflow']['OPENREQUESTSNOFOUND'] = 'No hay requerimiento abierto';
$lang['es_AR']['SiteTreeCMSWorkflow']['PUBLISHHEADER'] = '¿Quién puede publicar esto dentro del CMS?';
$lang['es_AR']['SiteTreeCMSWorkflow']['REQUEST_DELETEFROMLIVE_SUCCESS_MESSAGE'] = 'Se envió email de %s requiriendo su eliminación';
$lang['es_AR']['SiteTreeCMSWorkflow']['REQUEST_PUBLICATION_SUCCESS_MESSAGE'] = 'Se envió email de %s requiriendo su publicación';
$lang['es_AR']['SiteTreeCMSWorkflow']['STATUS_APPROVED'] = 'Aprobado';
$lang['es_AR']['SiteTreeCMSWorkflow']['STATUS_AWAITINGAPPROVAL'] = 'Esperando Aprobación';
$lang['es_AR']['SiteTreeCMSWorkflow']['STATUS_AWAITINGEDIT'] = 'Esperando su Edición';
$lang['es_AR']['SiteTreeCMSWorkflow']['STATUS_DENIED'] = 'Denegado';
$lang['es_AR']['SiteTreeCMSWorkflow']['STATUS_OPEN'] = 'Abierto';
$lang['es_AR']['SiteTreeCMSWorkflow']['STATUS_UNKNOWN'] = 'Desconocido';
$lang['es_AR']['SiteTreeCMSWorkflow']['WORKFLOWTABTITLE'] = 'Flujo de Trabajo';
$lang['es_AR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_APPROVED'] = 'Su requerimiento para eliminar la página "%s" ha sido aprobado';
$lang['es_AR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Por favor revise y elimine la página "%s" en su sitio';
$lang['es_AR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'Se solicita a usted revisar la página "%s"';
$lang['es_AR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_DENIED'] = 'Su requerimiento para eliminar la página "%s" ha sido rechazado';
$lang['es_AR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_APPROVED'] = 'Su requerimiento para publicar la página "%s" ha sido aprobado';
$lang['es_AR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Por favor revise y publique la página "%s" en su sitio';
$lang['es_AR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'Se solicita a usted revisar la página "%s"';
$lang['es_AR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_DENIED'] = 'Su requerimiento para publicar la página "%s" ha sido rechazado';
$lang['es_AR']['WorkflowRequest']['COMPAREDRAFTLIVELINK'] = 'Comparar los cambios entre la versión viva y el borrador cambiado';
$lang['es_AR']['WorkflowRequest']['EMAILCHANGEDSTATUS'] = '%s ha cambiado el estado del flujo de trabajo sobre';
$lang['es_AR']['WorkflowRequest']['EMAILDENIEDDELETION'] = '%s ha rechazado su requerimiento de eliminación sobre';
$lang['es_AR']['WorkflowRequest']['EMAILDENIEDPUBLICATION'] = '%s ha rechazado su requerimiento de publicación sobre';
$lang['es_AR']['WorkflowRequest']['EMAILGREETING'] = 'Hola %s';
$lang['es_AR']['WorkflowRequest']['EMAILHASBEENPUBLISHED'] = 'Usted escribió una página que ha sido publicada por %s';
$lang['es_AR']['WorkflowRequest']['EMAILRECENTLYUPDATED1'] = '%s ha actualizado recientemente la página titulada';
$lang['es_AR']['WorkflowRequest']['EMAILRECENTLYUPDATED2'] = 'y desea que los cambios sean publicados.';
$lang['es_AR']['WorkflowRequest']['EMAILREQUESTREMOVE'] = '%s desea quitar la página titulada';
$lang['es_AR']['WorkflowRequest']['EMAILTHANKS'] = 'Gracias.';
$lang['es_AR']['WorkflowRequest']['EMAIL_SUBJECT_GENERIC'] = 'El estado de flujo de trabajo de la página "%s" ha cambiado';
$lang['es_AR']['WorkflowRequest']['REVIEWANDDELETEPAGELINK'] = 'Revisar y eliminar la página en el CMS';
$lang['es_AR']['WorkflowRequest']['REVIEWANDPUBLISHPAGELINK'] = 'Revisar y publicar la página en el CMS';
$lang['es_AR']['WorkflowRequest']['REVIEWPAGELINK'] = 'Revisar la página en el CMS';
$lang['es_AR']['WorkflowRequest']['TITLE'] = 'Requerimiento de Flujo de Trabajo';
$lang['es_AR']['WorkflowRequest']['VIEWCHANGEDDRAFTLINK'] = 'Ver el borrador cambiado';
$lang['es_AR']['WorkflowRequest']['VIEWPUBLISHEDCHANGESLINK'] = 'Revisar los cámbios publicados en el CMS';
$lang['es_AR']['WorkflowRequest']['VIEWPUBLISHEDLINK'] = 'Ver la página en su sitio web';
$lang['es_AR']['WorkflowRequest']['VIEWUNPUBLISHEDCHANGESLINK'] = 'Comparar los cambios sin publicar en el CMS';
$lang['es_AR']['WorkflowRequestChange']['PLURALNAME'] = 'Requerimiento de Cambios en el Flujo de Trabajo';
$lang['es_AR']['WorkflowRequestChange']['SINGULARNAME'] = 'Requerimiento de Cambio en el Flujo de Trabajo';

?>