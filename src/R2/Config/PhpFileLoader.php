<?php

namespace R2\Config;

use InvalidArgumentException;

class PhpFileLoader implements FileLoaderInterface
{
    /**
     * Checks if such file type is supported.
     *
     * @param string $resource
     *
     * @return Boolean
     */
    public function supports($resource)
    {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION);
    }

    /**
     * Loads data.
     *
     * @param string $resource The filename
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function load($resource)
    {
        if (!file_exists($resource)) {
            throw new InvalidArgumentException('Resource not found');
        }

        return require($resource);
    }
}
