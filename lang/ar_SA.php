<?php

/**
 * Arabic (Saudi Arabia) language pack
 * @package modules: cms workflow
 * @subpackage i18n
 */

i18n::include_locale_file('modules: cms workflow', 'en_US');

global $lang;

if(array_key_exists('ar_SA', $lang) && is_array($lang['ar_SA'])) {
	$lang['ar_SA'] = array_merge($lang['en_US'], $lang['ar_SA']);
} else {
	$lang['ar_SA'] = $lang['en_US'];
}

$lang['ar_SA']['DeletionRequestSideReport']['TITLE'] = 'تسلسل العمل : بانتظار الحذف';
$lang['ar_SA']['MyWorkflowRequestsSideReport']['TITLE'] = 'تسلسل العمل : مراجعة الطلبات المعلقة';
$lang['ar_SA']['PublisherReviewSideReport']['TITLE'] = 'تسلسل العمل : بانتظار النشر';
$lang['ar_SA']['SiteTree']['EDITANYONE'] = 'أ] شخص يمكنه الدخول إلى إدارة المحتوى';
$lang['ar_SA']['SiteTree']['EDITONLYTHESE'] = 'فقط أشخاص محددين ( اختر من القائمة)';
$lang['ar_SA']['SiteTreeCMSWorkflow']['BUTTONDENYPUBLICATION'] = 'منع النشر';
$lang['ar_SA']['SiteTreeCMSWorkflow']['BUTTONREQUESTPUBLICATION'] = 'طلب النشر';
$lang['ar_SA']['SiteTreeCMSWorkflow']['BUTTONREQUESTREMOVAL'] = 'طلب الحذف';
$lang['ar_SA']['SiteTreeCMSWorkflow']['CLOSEDREQUESTSHEADER'] = 'إغلاق الطلبات';
$lang['ar_SA']['SiteTreeCMSWorkflow']['DENYDELECTIONMESSAGE'] = 'رفض الطلب و إعادة الصفحة إلى الإصدار الحالي. تبليغ %sبالإيميل d';
$lang['ar_SA']['SiteTreeCMSWorkflow']['DENYPUBLICATION_MESSAGE'] = 'رفض الطلب و إعادة الصفحة إلى الإصدار الحالي. تبليغ %sبالإيميل d';
$lang['ar_SA']['SiteTreeCMSWorkflow']['DIFFERENCESCOLUMN'] = 'الفروقات';
$lang['ar_SA']['SiteTreeCMSWorkflow']['DIFFERENCESLINK'] = 'عرض الفروقات إلى الإصدار الحالي';
$lang['ar_SA']['SiteTreeCMSWorkflow']['DIFFERENCESTHISCHANGECOLUMN'] = 'الفروقات في هذا التغيير';
$lang['ar_SA']['SiteTreeCMSWorkflow']['DIFFERENCESTOLIVECOLUMN'] = 'الفروقات إلى الإصدار الحالي';
$lang['ar_SA']['SiteTreeCMSWorkflow']['FIELDLABEL_AUTHOR'] = 'الكاتب';
$lang['ar_SA']['SiteTreeCMSWorkflow']['FIELDLABEL_PAGE'] = 'الصفحة';
$lang['ar_SA']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHER'] = 'الناشر';
$lang['ar_SA']['SiteTreeCMSWorkflow']['FIELDLABEL_PUBLISHERS'] = 'الناشرون';
$lang['ar_SA']['SiteTreeCMSWorkflow']['FIELDLABEL_STATUS'] = 'الحالة';
$lang['ar_SA']['SiteTreeCMSWorkflow']['OPENREQUESTHEADER'] = 'الطلبات المفتوحة';
$lang['ar_SA']['SiteTreeCMSWorkflow']['OPENREQUESTSNOFOUND'] = 'لا يوجد طلبات مفتوحة';
$lang['ar_SA']['SiteTreeCMSWorkflow']['PUBLISHHEADER'] = 'من يملك صلاحية النشر داخل نظام إدارة المحتوى ؟';
$lang['ar_SA']['SiteTreeCMSWorkflow']['REQUEST_DELETEFROMLIVE_SUCCESS_MESSAGE'] = 'تبليغ %s برفض طلب الحذف';
$lang['ar_SA']['SiteTreeCMSWorkflow']['REQUEST_PUBLICATION_SUCCESS_MESSAGE'] = 'تبليغ %s برفض طلب النشر';
$lang['ar_SA']['SiteTreeCMSWorkflow']['STATUS_APPROVED'] = 'تمت الموافقة';
$lang['ar_SA']['SiteTreeCMSWorkflow']['STATUS_AWAITINGAPPROVAL'] = 'بانتظار الموافقة';
$lang['ar_SA']['SiteTreeCMSWorkflow']['STATUS_AWAITINGEDIT'] = 'بانتظار التعديل';
$lang['ar_SA']['SiteTreeCMSWorkflow']['STATUS_DENIED'] = 'مرفوض';
$lang['ar_SA']['SiteTreeCMSWorkflow']['STATUS_OPEN'] = 'مفتوح';
$lang['ar_SA']['SiteTreeCMSWorkflow']['STATUS_UNKNOWN'] = 'غير معروف';
$lang['ar_SA']['SiteTreeCMSWorkflow']['WORKFLOWTABTITLE'] = 'تسلسل العمل';
$lang['ar_SA']['WorkflowDeletionRequest']['EMAIL_SUBJECT_APPROVED'] = 'تمت الموافقة على حذف "%s"';
$lang['ar_SA']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'فضلاً راجع واحذف الصفحة "%s" من موقعك';
$lang['ar_SA']['WorkflowDeletionRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'تقدمت بطلب مراجعة الصفحة "%s"';
$lang['ar_SA']['WorkflowDeletionRequest']['EMAIL_SUBJECT_DENIED'] = 'تم رفض طلبك لحذف الصفحة "%s"';
$lang['ar_SA']['WorkflowPublicationRequest']['EMAIL_SUBJECT_APPROVED'] = 'تمت الموافقة على طلبك لنشر الصفحة "%s"';
$lang['ar_SA']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGAPPROVAL'] = 'فضلاً راجع وانشر الصفحة "%s" في موقعك';
$lang['ar_SA']['WorkflowPublicationRequest']['EMAIL_SUBJECT_AWAITINGEDIT'] = 'قمت بطلب مراجعة الصفحة "%s"';
$lang['ar_SA']['WorkflowPublicationRequest']['EMAIL_SUBJECT_DENIED'] = 'تم رفض طلبك لنشر الصفحة "%s"';
$lang['ar_SA']['WorkflowRequest']['COMPAREDRAFTLIVELINK'] = 'مقارنة التغييرات بين الإصدار الحالي و المسودة المعدلة';
$lang['ar_SA']['WorkflowRequest']['EMAILCHANGEDSTATUS'] = '%s قام بتغيير حالة تسلسل العمل إلى';
$lang['ar_SA']['WorkflowRequest']['EMAILDENIEDDELETION'] = '%s قام برفض طلب الحذف على';
$lang['ar_SA']['WorkflowRequest']['EMAILDENIEDPUBLICATION'] = '%s قام برفض طلب النشر على';
$lang['ar_SA']['WorkflowRequest']['EMAILGREETING'] = 'أهلاً %s';
$lang['ar_SA']['WorkflowRequest']['EMAILHASBEENPUBLISHED'] = 'الصفحة التي كتبتها تم نشرها بواسطة %s';
$lang['ar_SA']['WorkflowRequest']['EMAILRECENTLYUPDATED1'] = '%s قام حديثاً بتغيير عنوان الصفحة';
$lang['ar_SA']['WorkflowRequest']['EMAILRECENTLYUPDATED2'] = 'و يود الحصول على التغييرات التي تم نشرها';
$lang['ar_SA']['WorkflowRequest']['EMAILREQUESTREMOVE'] = '%s يرغب في حذف عنوان الصفحة';
$lang['ar_SA']['WorkflowRequest']['EMAILTHANKS'] = 'شكراً جزيلاً';
$lang['ar_SA']['WorkflowRequest']['EMAIL_SUBJECT_GENERIC'] = 'تم تغيير تسلسل العمل للصفحة "%s"';
$lang['ar_SA']['WorkflowRequest']['REVIEWANDDELETEPAGELINK'] = 'مراجعة و حذف الصفحة في نظام إدارة المحتوى';
$lang['ar_SA']['WorkflowRequest']['REVIEWANDPUBLISHPAGELINK'] = 'مراجعة و نشر الصفحة في نظام إدارة المحتوى';
$lang['ar_SA']['WorkflowRequest']['REVIEWPAGELINK'] = 'مراجعة الصفحة في نظام إدارة المحتوى';
$lang['ar_SA']['WorkflowRequest']['TITLE'] = 'طلب تسلسل عمل';
$lang['ar_SA']['WorkflowRequest']['VIEWCHANGEDDRAFTLINK'] = 'عرض المسودات المتغيرة';
$lang['ar_SA']['WorkflowRequest']['VIEWPUBLISHEDCHANGESLINK'] = 'مراجعة التغيرات التي تم نشرها في نظام إدارة المحتوى';
$lang['ar_SA']['WorkflowRequest']['VIEWPUBLISHEDLINK'] = 'عرض هذه الصفحة في موقعك';
$lang['ar_SA']['WorkflowRequest']['VIEWUNPUBLISHEDCHANGESLINK'] = 'مقارنة التغيرات التي لم يتم نشرها في نظام إدارة المحتوى';
$lang['ar_SA']['WorkflowRequestChange']['PLURALNAME'] = 'تغييرات طلب تسلسل العمل';
$lang['ar_SA']['WorkflowRequestChange']['SINGULARNAME'] = 'تغيير طلب تسلسل العمل';

?>