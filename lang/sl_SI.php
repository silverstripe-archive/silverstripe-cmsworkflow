<?php

/**
 * Slovenian (Slovenia) language pack
 * @package cmsworkflow
 * @subpackage i18n
 */

i18n::include_locale_file('cmsworkflow', 'en_US');

global $lang;

if(array_key_exists('sl_SI', $lang) && is_array($lang['sl_SI'])) {
	$lang['sl_SI'] = array_merge($lang['en_US'], $lang['sl_SI']);
} else {
	$lang['sl_SI'] = $lang['en_US'];
}

$lang['sl_SI']['BatchApprovePages']['APPROVED_PAGES'] = 'Odobrenih %d strani, %d neuspešno';
$lang['sl_SI']['BatchApprovePages']['APPROVE_PAGES'] = 'Odobri';
$lang['sl_SI']['BatchApprovePages']['APPROVING_PAGES'] = 'Odobravanje strani';
$lang['sl_SI']['BatchPublishPages']['DELETE_FAILURE'] = 'Neuspešno brisanje %d strani z objavljenega spletišča.';
$lang['sl_SI']['BatchPublishPages']['DELETE_FAILURE_ONE'] = 'Neuspešno brisanje %d strani z objavljenega spletišča.';
$lang['sl_SI']['BatchPublishPages']['DELETE_SUCCESS'] = 'Izbrisanih %d strani z objavljenega spletišča.';
$lang['sl_SI']['BatchPublishPages']['DELETE_SUCCESS_ONE'] = 'Izbrisana %d stran z objavljenega spletišča.';
$lang['sl_SI']['BatchPublishPages']['FORCE_PUBLISH'] = 'Vsili objavo';
$lang['sl_SI']['BatchPublishPages']['PUBLISHING_PAGES'] = 'Objavljanje strani';
$lang['sl_SI']['BatchPublishPages']['PUBLISH_FAILURE'] = 'Neuspešno objavljanje %d strani.';
$lang['sl_SI']['BatchPublishPages']['PUBLISH_FAILURE_ONE'] = 'Neuspešno objavljanje %d strani.';
$lang['sl_SI']['BatchPublishPages']['PUBLISH_PAGES'] = 'Objavi';
$lang['sl_SI']['BatchPublishPages']['PUBLISH_SUCCESS'] = 'Objavljenih %d strani.';
$lang['sl_SI']['BatchPublishPages']['PUBLISH_SUCCESS_ONE'] = 'Objavljena %d stran.';
$lang['sl_SI']['BatchResetEmbargo']['ACTIONED_PAGES'] = 'Ponastavljen rok prepovedi na %d straneh, %d neuspešno';
$lang['sl_SI']['BatchResetEmbargo']['ACTION_TITLE'] = 'Ponastavi rok prepovedi';
$lang['sl_SI']['BatchResetEmbargo']['DOING_TEXT'] = 'Ponastavljanje roka prepovedi';
$lang['sl_SI']['BatchResetExpiry']['ACTIONED_PAGES'] = 'Ponastavljen rok veljavnosti na %d straneh, %d neuspešno';
$lang['sl_SI']['BatchResetExpiry']['ACTION_TITLE'] = 'Ponastavi rok veljavnosti';
$lang['sl_SI']['BatchResetExpiry']['DOING_TEXT'] = 'Ponastavljanje roka veljavnosti';
$lang['sl_SI']['BatchSetEmbargo']['ACTIONED_PAGES'] = 'Nastavljen rok prepovedi na %d straneh, %d neuspešno';
$lang['sl_SI']['BatchSetEmbargo']['ACTION_TITLE'] = 'Nastavi datum prepovedi';
$lang['sl_SI']['BatchSetEmbargo']['DOING_TEXT'] = 'Nastavljanje datuma prepovedi';
$lang['sl_SI']['BatchSetExpiry']['ACTIONED_PAGES'] = 'Nastavljen rok veljavnosti na %d straneh, %d neuspešno';
$lang['sl_SI']['BatchSetExpiry']['ACTION_TITLE'] = 'Nastavi rok veljavnosti';
$lang['sl_SI']['BatchSetExpiry']['DOING_TEXT'] = 'Nastavljanje roka veljavnosti';
$lang['sl_SI']['CMSWorkflowThreeStepFilters_PagesAwaitingApproval']['TITLE'] = 'Strani, ki čakajo odobritev';
$lang['sl_SI']['CMSWorkflowThreeStepFilters_PagesAwaitingPublishing']['TITLE'] = 'Strani, ki čakajo na objavo';
$lang['sl_SI']['LeftAndMain']['CHANGEDURL'] = '  URL spremenjen v \'%s\'';
$lang['sl_SI']['LeftAndMain']['SAVEDUP'] = 'Shranjeno';
$lang['sl_SI']['LeftAndMain']['STATUSTO'] = '  Stanje spremenjeno v \'%s\'';
$lang['sl_SI']['MyTwoStepDeletionRequestsSideReport']['TITLE'] = 'Potek: čakanje na brisanje';
$lang['sl_SI']['MyTwoStepPublicationRequestsSideReport']['TITLE'] = 'Potek: čakanje na objavo';
$lang['sl_SI']['MyTwoStepWorkflowRequestsSideReport']['TITLE'] = 'Potek: moje zahteve, ki čakajo na pregled';
$lang['sl_SI']['SiteTree']['EDITANYONE'] = 'Vsakdo, ki se lahko prijavi v CMS';
$lang['sl_SI']['SiteTree']['EDITINHERIT'] = 'Deduj s starševske strani';
$lang['sl_SI']['SiteTree']['EDITONLYTHESE'] = 'Samo sledeče osebe (izberite s seznama)';
$lang['sl_SI']['SiteTreeCMSWorkflow']['APPROVEDANDPUBLISHMESSAGE'] = 'Zahteva odobrena in spremembe objavljene v živi različici. Poslano po e-pošti %s.';
$lang['sl_SI']['SiteTreeCMSWorkflow']['APPROVEHEADER'] = 'Kdo lahko odobri zahteve v CMS?';
$lang['sl_SI']['SiteTreeCMSWorkflow']['APPROVEMESSAGE'] = 'Zahteva odobrena. E-pošta poslana %s.';
$lang['sl_SI']['SiteTreeCMSWorkflow']['AUTO_APPROVED'] = '(samodejno odobreno)';
$lang['sl_SI']['SiteTreeCMSWorkflow']['AUTO_DENIED'] = '(samodejno zavrnjeno)';
$lang['sl_SI']['SiteTreeCMSWorkflow']['AUTO_DENIED_DELETED'] = '(samodejno zavrnjeno, ko je bila stran izbrisana)';
$lang['sl_SI']['SiteTreeCMSWorkflow']['AUTO_DENIED_PUBLISHED'] = '(samodejno zavrnjeno, ko je bila stran objavljena)';
$lang['sl_SI']['SiteTreeCMSWorkflow']['BUTTONREQUESTPUBLICATION'] = 'Zahtevaj objavo';
$lang['sl_SI']['SiteTreeCMSWorkflow']['BUTTONREQUESTREMOVAL'] = 'Zahtevaj umik';
$lang['sl_SI']['SiteTreeCMSWorkflow']['CANCELREQUEST_MESSAGE'] = 'Preklicana zahteva za potek. Poslano po e-pošti %s';
$lang['sl_SI']['SiteTreeCMSWorkflow']['CHANGEREQUESTED'] = 'Zahtevali ste to spremembo. Poslano po e-pošti %s.';
$lang['sl_SI']['SiteTreeCMSWorkflow']['CLOSEDREQUESTSHEADER'] = 'Zaprte zahteve';
$lang['sl_SI']['SiteTreeCMSWorkflow']['COMMENT_MESSAGE'] = 'Komentirali ste to zahtevo za potek. Poslano po e-pošti %s.';
$lang['sl_SI']['SiteTreeCMSWorkflow']['DENYPUBLICATION_MESSAGE'] = 'Zavrnjena zahteva za potek in ponastavljena vsebina. Poslano po e-pošti %s';
$lang['sl_SI']['SiteTreeCMSWorkflow']['DIFFERENCESCOLUMN'] = 'Razlike';
$lang['sl_SI']['SiteTreeCMSWorkflow']['DIFFERENCESLINK'] = 'Pokaži razlike z živim';
$lang['sl_SI']['SiteTreeCMSWorkflow']['DIFFERENCESTHISCHANGECOLUMN'] = 'Razlike v tej spremembi';
$lang['sl_SI']['SiteTreeCMSWorkflow']['DIFFERENCESTOLIVECOLUMN'] = 'Razlike z živim';
$lang['sl_SI']['SiteTreeCMSWorkflow']['FIELDLABEL_AUTHOR'] = 'Avtor';
$lang['sl_SI']['SiteTreeCMSWorkflow']['FIELDLABEL_PAGE'] = 'Stran';
$lang['sl_SI']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHER'] = 'Izdajatelj';
$lang['sl_SI']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHERS'] = 'Izdajatelji';
$lang['sl_SI']['SiteTreeCMSWorkflow']['FIELDLABEL_STATUS'] = 'Stanje';
$lang['sl_SI']['SiteTreeCMSWorkflow']['NEXTREVIEWDATE'] = 'Datum naslednje revizije (pustite prazno, če je ne bo)';
$lang['sl_SI']['SiteTreeCMSWorkflow']['PAGEOWNER'] = 'Lastnik strani (bo odgovoren za revizije)';
$lang['sl_SI']['SiteTreeCMSWorkflow']['PUBLISHAPPROVEDHEADER'] = 'Kdo lahko objavi odobrene zahteve znotraj CMS?';
$lang['sl_SI']['SiteTreeCMSWorkflow']['PUBLISHHEADER'] = 'Kdo lahko to objavi znotraj CMS?';
$lang['sl_SI']['SiteTreeCMSWorkflow']['PUBLISHMESSAGE'] = 'Objavljene spremembe na živi različici. Poslano po e-pošti %s.';
$lang['sl_SI']['SiteTreeCMSWorkflow']['REVIEWFREQUENCY'] = 'Frekvenca revizij (datum revizije bo nastavljen tako daleč v prihodnost, ko bo stran objavljena.)';
$lang['sl_SI']['SiteTreeCMSWorkflow']['REVIEWHEADER'] = 'Revizija vsebine';
$lang['sl_SI']['SiteTreeCMSWorkflow']['STATUS_APPROVED'] = 'Odobreno';
$lang['sl_SI']['SiteTreeCMSWorkflow']['STATUS_AWAITINGAPPROVAL'] = 'Čakanje na odobritev';
$lang['sl_SI']['SiteTreeCMSWorkflow']['STATUS_AWAITINGEDIT'] = 'Čakanje na urejanje';
$lang['sl_SI']['SiteTreeCMSWorkflow']['STATUS_CANCELLED'] = 'Preklicano';
$lang['sl_SI']['SiteTreeCMSWorkflow']['STATUS_COMPLETED'] = 'Dokončano';
$lang['sl_SI']['SiteTreeCMSWorkflow']['STATUS_DENIED'] = 'Zavrnjeno';
$lang['sl_SI']['SiteTreeCMSWorkflow']['STATUS_OPEN'] = 'Odprto';
$lang['sl_SI']['SiteTreeCMSWorkflow']['STATUS_SCHEDULED'] = 'Načrtovano za objavo';
$lang['sl_SI']['SiteTreeCMSWorkflow']['WORKFLOWACTION_ACTION'] = 'Objavi spremembe';
$lang['sl_SI']['SiteTreeCMSWorkflow']['WORKFLOWACTION_APPROVE'] = 'Odobri';
$lang['sl_SI']['SiteTreeCMSWorkflow']['WORKFLOWACTION_REQUESTEDIT'] = 'Zahtevaj urejanje';
$lang['sl_SI']['SiteTreeCMSWorkflow']['WORKFLOW_ACTION_CANCEL'] = 'Prekliči';
$lang['sl_SI']['SiteTreeCMSWorkflow']['WORKFLOW_ACTION_COMMENT'] = 'Komentiraj';
$lang['sl_SI']['SiteTreeCMSWorkflow']['WORKFLOW_ACTION_DENY'] = 'Zavrni';
$lang['sl_SI']['SiteTreeCMSWorkflow']['WORKFLOW_ACTION_FAILED'] = 'Pri obdelavi vaše zahteve poteka je prišlo do napake.';
$lang['sl_SI']['SiteTreeCMSWorkflow']['WORKFLOW_ACTION_RESUBMIT'] = 'Pošlji znova';
$lang['sl_SI']['ThreeStepWorkflowPublicationRequestsNeedingApprovalSideReport']['TITLE'] = 'Potek: zahteve po objavi, ki jih moram odobriti';
$lang['sl_SI']['ThreeStepWorkflowPublicationRequestsNeedingPublishingSideReport']['TITLE'] = 'Potek: zahteve po objavi, ki jih moram objaviti';
$lang['sl_SI']['ThreeStepWorkflowRemovalRequestsNeedingApprovalSideReport']['TITLE'] = 'Potek: zahteve po umiku, ki jih moram odobriti';
$lang['sl_SI']['ThreeStepWorkflowRemovalRequestsNeedingPublishingSideReport']['TITLE'] = 'Potek: zahteve po umiku, ki jih moram objaviti';
$lang['sl_SI']['WorkflowDeletionRequest']['EMAIL_PARA_APPROVED'] = array(
	'%s je odobril vašo zahtevo po brisanju strani "%s" in jo je izbrisal z objavljenega spletišča.',
	50,
	'Uvodni odstavek za e-pismo poteka dela, z imenom in naslovom strani'
);
$lang['sl_SI']['WorkflowDeletionRequest']['EMAIL_PARA_AWAITINGAPPROVAL'] = array(
	'%s vas je prosil, da izbrišete stran "%s"',
	50,
	'Uvodni odstavek za e-pismo poteka dela, z imenom in naslovom strani'
);
$lang['sl_SI']['WorkflowDeletionRequest']['EMAIL_PARA_AWAITINGEDIT'] = array(
	'%s vas je prosil, da revidirate svojo zahtevo po brisanju strani "%s".',
	50,
	'Uvodni odstavek za e-pismo poteka dela, z imenom in naslovom strani'
);
$lang['sl_SI']['WorkflowDeletionRequest']['EMAIL_PARA_COMMENT'] = array(
	'%s je komentiral zahtevo po brisanju strani "%s".',
	50,
	'Uvodni odstavek za e-pismo poteka dela, z imenom in naslovom strani'
);
$lang['sl_SI']['WorkflowDeletionRequest']['EMAIL_PARA_DENIED'] = array(
	'%s je zavrnil vašo zahtevo po brisanju strani "%s".',
	50,
	'Uvodni odstavek za e-pismo poteka dela, z imenom in naslovom strani'
);
$lang['sl_SI']['WorkflowDeletionRequest']['EMAIL_SUBJECT_APPROVED'] = array(
	'Stran, izbrisana z objavljenega spletišča: "%s"',
	50,
	'Zadeva e-pisma z naslovom strani'
);
$lang['sl_SI']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = array(
	'Zahtevano brisanje strani: %s',
	50,
	'Zadeva e-pisma z naslovom strani'
);
$lang['sl_SI']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = array(
	'Zahtevana revizija: "%s"',
	50,
	'Zadeva e-pisma z naslovom strani'
);
$lang['sl_SI']['WorkflowDeletionRequest']['EMAIL_SUBJECT_COMMENT'] = array(
	'Komentar na zahtevo po reviziji: "%s"',
	50,
	'Zadeva e-pisma z naslovom strani'
);
$lang['sl_SI']['WorkflowDeletionRequest']['EMAIL_SUBJECT_DENIED'] = array(
	'Zavrnjeno brisanje: "%s"',
	50,
	'Zadeva e-pisma z naslovom strani'
);
$lang['sl_SI']['WorkflowDeletionRequest']['SETEXPIRY'] = 'Določen rok veljavnosti. Poslano po e-pošti %s';
$lang['sl_SI']['WorkflowPublicationRequest']['EMAIL_PARA_APPROVED'] = array(
	'%s je sprejel in objavil vaše spremembe strani "%s".',
	50,
	'Uvodni odstavek za e-pismo poteka dela, z imenom in naslovom strani'
);
$lang['sl_SI']['WorkflowPublicationRequest']['EMAIL_PARA_AWAITINGAPPROVAL'] = array(
	'%s vas je prosil, da revidirate in objavite naslednjo spremembo strani "%s"',
	50,
	'Uvodni odstavek za e-pismo poteka dela, z imenom in naslovom strani'
);
$lang['sl_SI']['WorkflowPublicationRequest']['EMAIL_PARA_AWAITINGEDIT'] = array(
	'%s vas je prosil, da revidirate svoje spremembe strani "%s".',
	50,
	'Uvodni odstavek za e-pismo poteka dela, z imenom in naslovom strani'
);
$lang['sl_SI']['WorkflowPublicationRequest']['EMAIL_PARA_COMMENT'] = array(
	'%s je komentiral zahtevano spremembo strani "%s".',
	50,
	'Uvodni odstavek za e-pismo poteka dela, z imenom in naslovom strani'
);
$lang['sl_SI']['WorkflowPublicationRequest']['EMAIL_PARA_DENIED'] = array(
	'%s je zavrnil vaše spremembe strani "%s".',
	50,
	'Uvodni odstavek za e-pismo poteka dela, z imenom in naslovom strani'
);
$lang['sl_SI']['WorkflowPublicationRequest']['EMAIL_SUBJECT_APPROVED'] = array(
	'Objavljena sprememba: "%s"',
	50,
	'Zadeva e-pisma z naslovom strani'
);
$lang['sl_SI']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = array(
	'Zahtevana objava spremembe: %s',
	50,
	'Zadeva e-pisma z naslovom strani'
);
$lang['sl_SI']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = array(
	'Zahtevana revizija: "%s"',
	50,
	'Zadeva e-pisma z naslovom strani'
);
$lang['sl_SI']['WorkflowPublicationRequest']['EMAIL_SUBJECT_COMMENT'] = array(
	'Komentar na zahtevo po objavi: "%s"',
	50,
	'Zadeva e-pisma z naslovom strani'
);
$lang['sl_SI']['WorkflowPublicationRequest']['EMAIL_SUBJECT_DENIED'] = array(
	'Zavrnjena sprememba: "%s"',
	50,
	'Zadeva e-pisma z naslovom strani'
);
$lang['sl_SI']['WorkflowRequest']['CHANGES_HEADING'] = 'Spremembe';
$lang['sl_SI']['WorkflowRequest']['COMMENT_HEADING'] = 'Komentar';
$lang['sl_SI']['WorkflowRequest']['EMAILGREETING'] = 'Živijo, %s';
$lang['sl_SI']['WorkflowRequest']['EMAILTHANKS'] = 'Hvala.';
$lang['sl_SI']['WorkflowRequest']['EMAIL_SUBJECT_GENERIC'] = array(
	'Stanje poteka strani "%s" je spremenjeno',
	50,
	'Zadeva e-pisma z naslovom strani'
);
$lang['sl_SI']['WorkflowRequest']['REVIEWPAGELINK'] = 'Preglej stran v CMS';
$lang['sl_SI']['WorkflowRequest']['TITLE'] = array(
	'Zahteva poteka',
	50,
	'Naslov za to zahtevo, prikazan npr. v pregledu stanj poteka strani'
);
$lang['sl_SI']['WorkflowRequest']['VIEWPUBLISHEDLINK'] = 'Pokaži to stran na spletnem mestu';
$lang['sl_SI']['WorkflowRequestChange']['PLURALNAME'] = array(
	'Spremembe zahteve poteka',
	50,
	'Množinsko ime predmeta, uporabljeno v seznamskih poljih in za splošno identifikacijo zbirke teh predmetov v vmesniku'
);
$lang['sl_SI']['WorkflowRequestChange']['SINGULARNAME'] = array(
	'Sprememba zahteve poteka',
	50,
	'Edninsko ime predmeta, uporabljeno v seznamskih poljih in za splošno identifikacijo posameznega predmeta v vmesniku'
);
$lang['sl_SI']['WorkflowSystemMember']['PLURALNAME'] = array(
	'Člani Sistema poteka',
	50,
	'Množinsko ime predmeta, uporabljeno v seznamskih poljih in za splošno identifikacijo zbirke teh predmetov v vmesniku'
);
$lang['sl_SI']['WorkflowSystemMember']['SINGULARNAME'] = array(
	'Član Sistema poteka',
	50,
	'Edninsko ime predmeta, uporabljeno v seznamskih poljih in za splošno identifikacijo posameznega predmeta v vmesniku'
);
$lang['sl_SI']['WorkflowThreeStepRequest']['PUBLISHMESSAGE'] = 'Spremembe objavljene na živi različici. Poslano po e-pošti %s.';

?>