<?php

/**
 * Turkish (Turkey) language pack
 * @package cmsworkflow
 * @subpackage i18n
 */

i18n::include_locale_file('cmsworkflow', 'en_US');

global $lang;

if(array_key_exists('tr_TR', $lang) && is_array($lang['tr_TR'])) {
	$lang['tr_TR'] = array_merge($lang['en_US'], $lang['tr_TR']);
} else {
	$lang['tr_TR'] = $lang['en_US'];
}

$lang['tr_TR']['DeletionRequestSideReport']['TITLE'] = 'İş Akışı: Silinmeyi Bekliyor';
$lang['tr_TR']['MyWorkflowRequestsSideReport']['TITLE'] = 'İş Akışı: Talepler İnceleniyor';
$lang['tr_TR']['PublisherReviewSideReport']['TITLE'] = 'İş Akışı: Yayınlanmayı Bekliyor';
$lang['tr_TR']['SiteTree']['EDITANYONE'] = 'İYS\'ne giriş yapabilen herkes';
$lang['tr_TR']['SiteTree']['EDITONLYTHESE'] = 'Sadece şu kişiler (listeden seç)';
$lang['tr_TR']['SiteTreeCMSWorkflow']['BUTTONDENYPUBLICATION'] = 'Yayınlamayı Reddet';
$lang['tr_TR']['SiteTreeCMSWorkflow']['BUTTONREQUESTPUBLICATION'] = 'Yayınlama Talebi';
$lang['tr_TR']['SiteTreeCMSWorkflow']['BUTTONREQUESTREMOVAL'] = 'Silme Talebi';
$lang['tr_TR']['SiteTreeCMSWorkflow']['CLOSEDREQUESTSHEADER'] = 'Kapanmış Talepler';
$lang['tr_TR']['SiteTreeCMSWorkflow']['DENYDELECTIONMESSAGE'] = 'Talep reddedildi ve sayfa mevcut versiyonuna döndürüldü.  %s adresine eposta atıldı.';
$lang['tr_TR']['SiteTreeCMSWorkflow']['DENYPUBLICATION_MESSAGE'] = 'Talep reddedildi ve sayfa mevcut sürümüne döndürüldü. %s adresine eposta gönderildi.';
$lang['tr_TR']['SiteTreeCMSWorkflow']['DIFFERENCESCOLUMN'] = 'Farklılıklar';
$lang['tr_TR']['SiteTreeCMSWorkflow']['DIFFERENCESLINK'] = 'Mevcut olandan farklarını göster';
$lang['tr_TR']['SiteTreeCMSWorkflow']['DIFFERENCESTHISCHANGECOLUMN'] = 'Bu düzenlemedeki farklılıklar';
$lang['tr_TR']['SiteTreeCMSWorkflow']['DIFFERENCESTOLIVECOLUMN'] = 'Mevcut olandan farkları';
$lang['tr_TR']['SiteTreeCMSWorkflow']['FIELDLABEL_AUTHOR'] = 'Yazan';
$lang['tr_TR']['SiteTreeCMSWorkflow']['FIELDLABEL_PAGE'] = 'Sayfa';
$lang['tr_TR']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHER'] = 'Yayınlayan';
$lang['tr_TR']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHERS'] = 'Yayınlayanlar';
$lang['tr_TR']['SiteTreeCMSWorkflow']['FIELDLABEL_STATUS'] = 'Durum';
$lang['tr_TR']['SiteTreeCMSWorkflow']['OPENREQUESTHEADER'] = 'Açık Talepler';
$lang['tr_TR']['SiteTreeCMSWorkflow']['OPENREQUESTSNOFOUND'] = 'Açık talep bulunamadı';
$lang['tr_TR']['SiteTreeCMSWorkflow']['PUBLISHHEADER'] = 'Bunu İYS içerisinde kimler yayınlayabilir?';
$lang['tr_TR']['SiteTreeCMSWorkflow']['REQUEST_DELETEFROMLIVE_SUCCESS_MESSAGE'] = '%s silme talebi eposta ile gönderildi.';
$lang['tr_TR']['SiteTreeCMSWorkflow']['REQUEST_PUBLICATION_SUCCESS_MESSAGE'] = 'Yayınlanma talebi %s adresine eposta olarak gönderildi';
$lang['tr_TR']['SiteTreeCMSWorkflow']['STATUS_APPROVED'] = 'Onaylandı';
$lang['tr_TR']['SiteTreeCMSWorkflow']['STATUS_AWAITINGAPPROVAL'] = 'Onay Bekliyor';
$lang['tr_TR']['SiteTreeCMSWorkflow']['STATUS_AWAITINGEDIT'] = 'Düzenleme Bekliyor';
$lang['tr_TR']['SiteTreeCMSWorkflow']['STATUS_DENIED'] = 'Reddedildi';
$lang['tr_TR']['SiteTreeCMSWorkflow']['STATUS_OPEN'] = 'Açık';
$lang['tr_TR']['SiteTreeCMSWorkflow']['STATUS_UNKNOWN'] = 'Bilinmeyen';
$lang['tr_TR']['SiteTreeCMSWorkflow']['WORKFLOWTABTITLE'] = 'İş Akışı';
$lang['tr_TR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_APPROVED'] = '"%s" sayfası için yapmış olduğunuz silme talebi onaylandı';
$lang['tr_TR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Lütfen "%s" sayfasını gözden geçirip, sitenizden siliniz';
$lang['tr_TR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = '"%s" sayfasını gözden geçirmeyi talep ettiniz';
$lang['tr_TR']['WorkflowDeletionRequest']['EMAIL_SUBJECT_DENIED'] = '"%s" sayfası için yapmış olduğunuz silme talebi reddedildi';
$lang['tr_TR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_APPROVED'] = '"%s" sayfası için yapmış olduğunuz yayınlama talebi onaylandı';
$lang['tr_TR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'Lütfen "%s" sayfasını gözden geçirip, sitenizde yayınlayınız';
$lang['tr_TR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = '"%s" sayfasını gözden geçirmeyi talep ettiniz';
$lang['tr_TR']['WorkflowPublicationRequest']['EMAIL_SUBJECT_DENIED'] = '"%s" sayfası için yapmış olduğunuz yayınlama talebi reddedildi';
$lang['tr_TR']['WorkflowRequest']['COMPAREDRAFTLIVELINK'] = 'Mevcut olan ile değiştirilmiş taslağı karşılaştır';
$lang['tr_TR']['WorkflowRequest']['EMAILCHANGEDSTATUS'] = '%s iş akışı durumunu değiştirdi:';
$lang['tr_TR']['WorkflowRequest']['EMAILDENIEDDELETION'] = '%s silme talebinizi reddetti:';
$lang['tr_TR']['WorkflowRequest']['EMAILDENIEDPUBLICATION'] = '%s yayınlanma talebinizi reddetti:';
$lang['tr_TR']['WorkflowRequest']['EMAILGREETING'] = 'Merhaba %s';
$lang['tr_TR']['WorkflowRequest']['EMAILHASBEENPUBLISHED'] = 'Hazırladığınız sayfa %s tarafından yayınlandı';
$lang['tr_TR']['WorkflowRequest']['EMAILRECENTLYUPDATED1'] = '%s sayfa başlığını güncelledi';
$lang['tr_TR']['WorkflowRequest']['EMAILRECENTLYUPDATED2'] = 've değişiklikleri yayınlamak istiyor.';
$lang['tr_TR']['WorkflowRequest']['EMAILREQUESTREMOVE'] = '%s sayfa başlığını kaldırmak istiyor';
$lang['tr_TR']['WorkflowRequest']['EMAILTHANKS'] = 'Teşekkürler.';
$lang['tr_TR']['WorkflowRequest']['EMAIL_SUBJECT_GENERIC'] = '"%s" sayfası için iş akışı durumu değiştirildi';
$lang['tr_TR']['WorkflowRequest']['REVIEWANDDELETEPAGELINK'] = 'İYS içindeki sayfayı gözden geçir ve sil';
$lang['tr_TR']['WorkflowRequest']['REVIEWANDPUBLISHPAGELINK'] = 'İYS içindeki sayfayı gözden geçir ve yayınla';
$lang['tr_TR']['WorkflowRequest']['REVIEWPAGELINK'] = 'İYS içindeki sayfayı gözden geçir';
$lang['tr_TR']['WorkflowRequest']['TITLE'] = 'İş Akışı Talebi';
$lang['tr_TR']['WorkflowRequest']['VIEWCHANGEDDRAFTLINK'] = 'Değiştirilmiş taslağı görüntüle';
$lang['tr_TR']['WorkflowRequest']['VIEWPUBLISHEDCHANGESLINK'] = 'İYS içinde yayınlanmış sayfayı gözden geçir';
$lang['tr_TR']['WorkflowRequest']['VIEWPUBLISHEDLINK'] = 'Websitenizdeki sayfayı görüntüle';
$lang['tr_TR']['WorkflowRequest']['VIEWUNPUBLISHEDCHANGESLINK'] = 'İYS içerisinde yayınlanmamış değişiklikleri karşılaştır';
$lang['tr_TR']['WorkflowRequestChange']['PLURALNAME'] = 'İş Akışı Talep Değişiklikleri';
$lang['tr_TR']['WorkflowRequestChange']['SINGULARNAME'] = 'İş Akışı Talep Değişikliği';

?>