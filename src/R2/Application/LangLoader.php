<?php

namespace R2\Application;

use R2\Translation\LoaderInterface as I18nLoaderInterface;
use R2\Config\YamlFileLoader;
use R2\Config\CachedFileLoader;

class LangLoader implements I18nLoaderInterface
{
    /** @var string */
    protected $localeDir;
    /** @var CachedFileLoader */
    protected $fileLoader;

    /**
     * Constructor.
     *
     * @param string $localeDir
     * @param string $cacheDir
     */
    public function __construct($localeDir, $cacheDir)
    {
        $this->localeDir = $localeDir;
        $this->fileLoader = new CachedFileLoader([new YamlFileLoader()], $cacheDir.'/%s.ser'); // weird! hardcoded
    }

    /**
     * Load domain translations.
     *
     * @param string $locale
     * @param string $domain
     *
     * @return mixed
     */
    public function load($locale, $domain)
    {
        $file = "{$this->localeDir}/{$domain}.{$locale}.yml";

        return file_exists($file) ? $this->fileLoader->load($file) : false;
    }

    /**
     * Checks if domain translations exist.
     *
     * @param string $locale
     * @param string $domain
     *
     * @return Boolean
     */
    public function exists($locale, $domain)
    {
        $file = "{$this->localeDir}/{$domain}.{$locale}.yml";

        return file_exists($file);
    }
}
