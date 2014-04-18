<?php

namespace R2\Translation;

/**
 * Gettext system
 */
class Translator implements TranslatorInterface
{
    protected $loader;
    protected $base;
    protected $locale;
    protected $fallbackLocale;
    protected $fallbackDomain;

    /**
     * Constructor.
     * 
     * @param LoaderInterface $loader
     * @param string          $fallbackLocale
     * @param string          $fallbackDomain
     */
    public function __construct(LoaderInterface $loader, $fallbackLocale = 'en', $fallbackDomain = 'common')
    {
        $this->loader         = $loader;
        $this->base           = [];
        $this->locale         =
        $this->fallbackLocale = $fallbackLocale;
        $this->fallbackDomain = $fallbackDomain;
    }

    /**
     * Gets current locale.
     * 
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets current locale.
     * Provides a fluent interface.
     * 
     * @param string $locale
     * @return I18n
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        
        return $this;
    }

    /**
     * Translates the given message.
     * 
     * @param string      $token The message
     * @param string|null $name  Domain
     * 
     * @return string
     */
    public function t($token, $domain = null)
    {
        $locale = $locale2 = $this->locale;
        if (!isset($domain)) {
            $domain = $this->fallbackDomain;
        }
        if (isset($this->base[$locale][$domain])) {
            $article = $this->base[$locale][$domain];
        } else {
            if (!$this->loader->exists($locale, $domain)) {
                $locale2 = substr($locale, 0, strpos($locale, '_'));
                if ($locale2 === '' || !$this->loader->exists($locale2, $domain)) {
                    $locale2 = $this->fallbackLocale;
                }
            }
            $article = $this->base[$locale][$domain] = $this->loader->load($locale2, $domain);
        }

        if (!isset($article[$token])) {
            return $token;
        }

        return $article[$token];
    }
}
