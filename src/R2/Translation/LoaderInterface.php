<?php

namespace R2\Translation;

interface LoaderInterface
{
    /**
     * Load domain translations.
     *
     * @param string $locale
     * @param string $domain
     *
     * @return mixed
     */
    public function load($locale, $domain);
    /**
     * Checks if domain translations exist.
     *
     * @param string $locale
     * @param string $domain
     *
     * @return Boolean
     */
    public function exists($locale, $domain);
}
