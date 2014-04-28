<?php
/*
 * Some usefull functions with short and clear names.
 *
 * DO NOT INCLUDE THIS FILE DIRECTLY
 * It depends on static var of Standard Extension class
 */
use R2\Templating\Extensions\Standard;

/**
 * Get current user
 * @return object
 */
function user()
{
    return Standard::$user;
}

/**
 * Shortcut to htmlspecialchars()
 * @param string $str
 * @return string
 */
function e($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Shortcut to Internationalization component
 * @param string $token
 * @param string $domain
 * @return string
 */
function t($token, $domain = 'common')
{
    return Standard::$i18n->t($token, $domain);
}

/**
 * Get full list of locales
 * @return array
 */
function locales()
{
    return Standard::$locales;
}

/**
 * Get current locale
 * @return string
 */
function currentLocale()
{
    return Standard::$locale;
}

/**
 * Get current locale label
 * For ex.: if locale is "en_US" then locale label is "en"
 * @return string
 */
function currentLocaleLabel()
{
    return Standard::$localeLabel;
}
