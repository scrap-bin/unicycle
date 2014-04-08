<?php

namespace R2\Config;

use InvalidArgumentException as ArgsException;

class JsonFileLoader implements FileLoaderInterface
{
    /**
     * Check if such file type is supported
     * @param  string  $resource
     * @return Boolean
     */
    public function supports($resource)
    {
        return is_string($resource) && 'json' === pathinfo($resource, PATHINFO_EXTENSION);
    }

    /**
     * Loads data.
     * @param  string                    $resource The filename
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function load($resource)
    {
        if (!file_exists($resource)) {
            throw new ArgsException('Resource not found');
        }

        return json_decode(file_get_contents($resource), true);
    }
}
