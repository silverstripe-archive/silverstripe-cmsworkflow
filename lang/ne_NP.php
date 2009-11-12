<?php

/**
 * Nepali (Nepal) language pack
 * @package cmsworkflow
 * @subpackage i18n
 */

i18n::include_locale_file('cmsworkflow', 'en_US');

global $lang;

if(array_key_exists('ne_NP', $lang) && is_array($lang['ne_NP'])) {
	$lang['ne_NP'] = array_merge($lang['en_US'], $lang['ne_NP']);
} else {
	$lang['ne_NP'] = $lang['en_US'];
}


?>