<?php

namespace R2\Application;

use R2\Translation\LoaderInterface as I18nLoaderInterface;
use R2\Config\YamlFileLoader;
use R2\Config\CachedFileLoader;

class LangLoader implements I18nLoaderInterface
{
    /** @var string */
    protected $langDir;
    /** @var CachedFileLoader */
    protected $fileLoader;

    /**
     * Constructor
     * @param string $langDir
     * @param string $cacheDir
     */
    public function __construct($langDir, $cacheDir)
    {
        $this->langDir = $langDir;
        $this->fileLoader = new CachedFileLoader([new YamlFileLoader()], $cacheDir.'/%s.ser');
    }

    /**
     * Load domain translations
     * @param  string $language
     * @param  string $domain
     * @return mixed
     */
    public function load($language, $domain)
    {
        $file = "{$this->langDir}/{$domain}.{$language}.yml";

        return $this->fileLoader->load($file);
    }

    /**
     * Check if domain translations exist
     * @param  string $language
     * @param  string $domain
     * @return array
     */
    public function exists($language, $domain)
    {
        $file = "{$this->langDir}/{$domain}.{$language}.yml";

        return file_exists($file);
    }
}
