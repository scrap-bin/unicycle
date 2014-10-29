<?php

namespace R2\Config;

/**
 * Wrapper to cache other loader(s).
 */
class CachedFileLoader extends SerializedFileLoader
{
    private $loaders;
    private $template;

    /**
     * Constructor.
     *
     * @param mixed  $loader   File loader or array of loaders
     * @param string $template Resource filename template
     */
    public function __construct($loader, $template)
    {
        $this->loaders  = is_array($loader) ? $loader : [$loader];
        $this->template = $template;
        foreach ($this->loaders as $loader) {
            if (!($loader instanceof FileLoaderInterface)) {
                throw new \InvalidArgumentException('Loader is not cacheable');
            }
        }
    }

    /**
     * Checks if such file type is supported.
     *
     * @param string $resource
     *
     * @return boolean
     */
    public function supports($resource)
    {
        if (is_string($resource)) {
            foreach ($this->loaders as $loader) {
                if ($loader->supports($resource)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Loads data.
     *
     * @param string $resource The filename
     *
     * @return mixed
     */
    public function load($resource)
    {
        $cacheFileName = $this->getCacheFileName($resource);
        $isUpToDate = true;

        if (!file_exists($cacheFileName)) {
            $isUpToDate = false;
        } else {
            $mtime = filemtime($cacheFileName);
            if (!file_exists($resource) || filemtime($resource) > $mtime) {
                unlink($cacheFileName);
                $isUpToDate = false;
            }
        }

        $result = false;
        if ($isUpToDate) {
            $result = parent::load($cacheFileName);
        } else {
            foreach ($this->loaders as $loader) {
                if ($loader->supports($resource)) {
                    $result = $loader->load($resource);
                    break;
                }
            }
            if ($result !== false) {
                file_put_contents($cacheFileName, serialize($result));
            }
        }

        $this->fileName = $resource;

        return $result;
    }

    /**
     * Composes template filename.
     *
     * @param string $resource
     *
     * @return string
     */
    private function getCacheFileName($resource)
    {
        return sprintf($this->template, md5($resource));
    }
}
