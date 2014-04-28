<?php

namespace R2\Templating\Extensions;

class Standard
{
    public static $user;
    public static $i18n;
    public static $locale;
    public static $locales;
    public static $localeLabel;

    public function __construct($user, $i18n, array $locales, $locale)
    {
        self::$user    = $user;
        self::$i18n    = $i18n;
        self::$locale  = $locale;
        self::$locales = $locales;
        self::$localeLabel = false === ($p = strpos($locale, '_')) ? $locale : substr($locale, 0, $p);
        require_once __DIR__.'/standard_shortcuts.php';
    }
}
