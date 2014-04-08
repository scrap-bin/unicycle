<?php

namespace R2\Config;

use InvalidArgumentException as ArgsException;

class YamlFileLoader implements FileLoaderInterface
{
    /**
     * Check if such file type is supported
     * @param  string  $resource
     * @return Boolean
     */
    public function supports($resource)
    {
        return is_string($resource) && 'yml' === pathinfo($resource, PATHINFO_EXTENSION);
    }

    /**
     * Load data
     * @param  string                    $resource The filename
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function load($resource)
    {
        if (!file_exists($resource)) {
            throw new ArgsException('Resource not found');
        }

        return yaml_parse_file($resource);
    }
}
