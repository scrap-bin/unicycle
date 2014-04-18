<?php

namespace R2\Translation;

interface TranslatorInterface
{
    /**
     * Gets current locale.
     *
     * @return string
     */
    public function getLocale();
    /**
     * Sets current locale.
     *
     * @param  string $locale
     * @return $this
     */
    public function setLocale($locale);
    /**
     * Translates the given message.
     * Provides a fluent interface.
     *
     * @param string      $token The message
     * @param string|null $name  Domain
     *
     * @return string
     */
    public function t($token, $domain = null);
}
