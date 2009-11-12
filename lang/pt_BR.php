<?php

/**
 * Portuguese (Brazil) language pack
 * @package cmsworkflow
 * @subpackage i18n
 */

i18n::include_locale_file('cmsworkflow', 'en_US');

global $lang;

if(array_key_exists('pt_BR', $lang) && is_array($lang['pt_BR'])) {
	$lang['pt_BR'] = array_merge($lang['en_US'], $lang['pt_BR']);
} else {
	$lang['pt_BR'] = $lang['en_US'];
}

$lang['pt_BR']['DeletionRequestSideReport']['TITLE'] = 'Workflow: Esperando a exclusão';
$lang['pt_BR']['MyWorkflowRequestsSideReport']['TITLE'] = 'Workflow: Minhas revisões pendentes';
$lang['pt_BR']['PublisherReviewSideReport']['TITLE'] = 'Workflow: Esperando publicação';
$lang['pt_BR']['SiteTree']['EDITANYONE'] = 'Todos que podem entrar no sistema';
$lang['pt_BR']['SiteTree']['EDITONLYTHESE'] = 'Apenas estas pessoas(Escolha na lista)';
$lang['pt_BR']['SiteTreeCMSWorkflow']['BUTTONDENYPUBLICATION'] = 'Proibir publicação';
$lang['pt_BR']['SiteTreeCMSWorkflow']['BUTTONREQUESTPUBLICATION'] = 'Requisitar Publicação';
$lang['pt_BR']['SiteTreeCMSWorkflow']['BUTTONREQUESTREMOVAL'] = 'Requisitar remoção';
$lang['pt_BR']['SiteTreeCMSWorkflow']['CLOSEDREQUESTSHEADER'] = 'Requisições fechadas';
$lang['pt_BR']['SiteTreeCMSWorkflow']['DENYDELECTIONMESSAGE'] = 'Requisição negada. Página já foi publicada. Enviado %s d';
$lang['pt_BR']['SiteTreeCMSWorkflow']['DENYPUBLICATION_MESSAGE'] = 'Requisição negada. Página já foi publicada. Enviado %s d';
$lang['pt_BR']['SiteTreeCMSWorkflow']['DIFFERENCESCOLUMN'] = 'Diferenças';
$lang['pt_BR']['SiteTreeCMSWorkflow']['DIFFERENCESLINK'] = 'Mostre as diferenças com a publicada';
$lang['pt_BR']['SiteTreeCMSWorkflow']['DIFFERENCESTHISCHANGECOLUMN'] = 'Diferenças nestas mudanças';
$lang['pt_BR']['SiteTreeCMSWorkflow']['DIFFERENCESTOLIVECOLUMN'] = 'Diferenças com a publicada';
$lang['pt_BR']['SiteTreeCMSWorkflow']['FIELDLABEL_AUTHOR'] = 'Autor';
$lang['pt_BR']['SiteTreeCMSWorkflow']['FIELDLABEL_PAGE'] = 'Página';
$lang['pt_BR']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHER'] = 'Publicador';
$lang['pt_BR']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHERS'] = 'Publicadores';
$lang['pt_BR']['SiteTreeCMSWorkflow']['FIELDLABEL_STATUS'] = 'Status';
$lang['pt_BR']['SiteTreeCMSWorkflow']['OPENREQUESTHEADER'] = 'Abrir requisições';
$lang['pt_BR']['SiteTreeCMSWorkflow']['OPENREQUESTSNOFOUND'] = 'Nenhuma requisição aberta foi encontrada';
$lang['pt_BR']['SiteTreeCMSWorkflow']['PUBLISHHEADER'] = 'Quem pode publicar artigo?';
$lang['pt_BR']['SiteTreeCMSWorkflow']['REQUEST_DELETEFROMLIVE_SUCCESS_MESSAGE'] = 'Enviar requisitar de deleção do %s ';
$lang['pt_BR']['SiteTreeCMSWorkflow']['REQUEST_PUBLICATION_SUCCESS_MESSAGE'] = 'Enviar requisitar de publicação do %s ';
$lang['pt_BR']['SiteTreeCMSWorkflow']['STATUS_APPROVED'] = 'Aprovado';
$lang['pt_BR']['SiteTreeCMSWorkflow']['STATUS_AWAITINGAPPROVAL'] = 'Esperando Aprovação';
$lang['pt_BR']['SiteTreeCMSWorkflow']['STATUS_AWAITINGEDIT'] = 'Esperando Edição';
$lang['pt_BR']['SiteTreeCMSWorkflow']['STATUS_DENIED'] = 'Negado';
$lang['pt_BR']['SiteTreeCMSWorkflow']['STATUS_OPEN'] = 'Abrir';
$lang['pt_BR']['SiteTreeCMSWorkflow']['STATUS_UNKNOWN'] = 'Desconhecido';
$lang['pt_BR']['SiteTreeCMSWorkflow']['WORKFLOWTABTITLE'] = 'Workflow';
$lang['pt_BR']['WorkflowDeletionRequest']['EMAIL_PARA_APPROVED'] = '%s aprovou o pedido de excluir a página "%s" e deletou do site publicado.';
$lang['pt_BR']['WorkflowDeletionRequest']['EMAIL_PARA_AWAITINGAPPROVAL'] = '%s pediu que você exclua a página  "%s"';
$lang['pt_BR']['WorkflowDeletionRequest']['EMAIL_PARA_AWAITINGEDIT'] = '%s solicitou que você reconsidere o seu pedido de exlcuir a página  "%s".';
$lang['pt_BR']['WorkflowDeletionRequest']['EMAIL_PARA_COMMENT'] = '%s comentou no pedido de excluir a página  "%s" page.';
$lang['pt_BR']['WorkflowDeletionRequest']['EMAIL_PARA_DENIED'] = '%s rejeitou o seu pedido de exlcuir a página  "%s".';
$lang['pt_BR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_APPROVED'] = 'Seu pedido de exclusão para "%s" foi aprovado';
$lang['pt_BR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Por favor, revise e exclua a "%s" página do seu site';
$lang['pt_BR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'Você solicitou uma revisão para página "%s"';
$lang['pt_BR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_COMMENT'] = 'Comentário no pedido de exlcusão: "%s"';
$lang['pt_BR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_DENIED'] = 'Seu pedido de exclusão para página "%s" foi negado';
$lang['pt_BR']['WorkflowPublicationRequest']['EMAIL_PARA_APPROVED'] = '%s aprovou e publicou as mudanças na página "%s".';
$lang['pt_BR']['WorkflowPublicationRequest']['EMAIL_PARA_AWAITINGAPPROVAL'] = '%s pediu que você revise e publique a seguinte mudança para a página "%s"';
$lang['pt_BR']['WorkflowPublicationRequest']['EMAIL_PARA_AWAITINGEDIT'] = '%s pediu que você revise suas mudanças na página "%s".';
$lang['pt_BR']['WorkflowPublicationRequest']['EMAIL_PARA_COMMENT'] = '%s comentou no pedido de mudança da página "%s".';
$lang['pt_BR']['WorkflowPublicationRequest']['EMAIL_PARA_DENIED'] = '%s rejeitou as mudanças na página "%s" page.';
$lang['pt_BR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_APPROVED'] = 'Seu pedido de publicação para a página "%s" foi aprovado';
$lang['pt_BR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Por favor, revise e publique a página "%s" no seu site';
$lang['pt_BR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'Você solicitou uma revisão para a página "%s"';
$lang['pt_BR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_COMMENT'] = 'Comentário no pedido de publicação: "%s"';
$lang['pt_BR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_DENIED'] = 'Seu pedido de publicação para a página "%s" foi negado';
$lang['pt_BR']['WorkflowRequest']['COMPAREDRAFTLIVELINK'] = 'Compra as mudanças entre o publicado e o rascunho';
$lang['pt_BR']['WorkflowRequest']['EMAILCHANGEDSTATUS'] = '%s mudou o status do workflow em';
$lang['pt_BR']['WorkflowRequest']['EMAILDENIEDDELETION'] = '%s negou sua requisição de exclusão em';
$lang['pt_BR']['WorkflowRequest']['EMAILDENIEDPUBLICATION'] = '%s negou sua requisição de publicação em';
$lang['pt_BR']['WorkflowRequest']['EMAILGREETING'] = 'Ola %s';
$lang['pt_BR']['WorkflowRequest']['EMAILHASBEENPUBLISHED'] = 'A página que você criou foi publicada por  %s';
$lang['pt_BR']['WorkflowRequest']['EMAILRECENTLYUPDATED1'] = '%s atualmente atualizou o título da página';
$lang['pt_BR']['WorkflowRequest']['EMAILRECENTLYUPDATED2'] = 'e gostaria de ter as mudanças publicadas';
$lang['pt_BR']['WorkflowRequest']['EMAILREQUESTREMOVE'] = '%s gostaria de remover a página intitulada';
$lang['pt_BR']['WorkflowRequest']['EMAILTHANKS'] = 'Obrigado.';
$lang['pt_BR']['WorkflowRequest']['EMAIL_SUBJECT_GENERIC'] = 'O status do fluxo da página "%s" foi alterado';
$lang['pt_BR']['WorkflowRequest']['REVIEWANDDELETEPAGELINK'] = 'Rever e excluir página no sistema';
$lang['pt_BR']['WorkflowRequest']['REVIEWANDPUBLISHPAGELINK'] = 'Rever e publicar página no sistema';
$lang['pt_BR']['WorkflowRequest']['REVIEWPAGELINK'] = 'Rever página no sistema';
$lang['pt_BR']['WorkflowRequest']['TITLE'] = 'Fluxo de requisição';
$lang['pt_BR']['WorkflowRequest']['VIEWCHANGEDDRAFTLINK'] = 'Ver mudanças no rascunho';
$lang['pt_BR']['WorkflowRequest']['VIEWPUBLISHEDCHANGESLINK'] = 'Rever mudanças publicadas no sistema';
$lang['pt_BR']['WorkflowRequest']['VIEWPUBLISHEDLINK'] = 'Ver as páginas do site';
$lang['pt_BR']['WorkflowRequest']['VIEWUNPUBLISHEDCHANGESLINK'] = 'Compara mudanças não publicadas';
$lang['pt_BR']['WorkflowRequestChange']['PLURALNAME'] = 'Mudanças no fluxo de requisição';
$lang['pt_BR']['WorkflowRequestChange']['SINGULARNAME'] = 'Fluxo de Pedido de Mudança';

?>