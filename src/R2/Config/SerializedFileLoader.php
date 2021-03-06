<?php

namespace R2\Config;

use InvalidArgumentException;

class SerializedFileLoader implements FileLoaderInterface
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
        return is_string($resource) && 'ser' === pathinfo($resource, PATHINFO_EXTENSION);
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

        return unserialize(file_get_contents($resource));
    }
}
