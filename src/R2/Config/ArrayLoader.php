<?php

namespace R2\Config;

class ArrayLoader implements LoaderInterface
{
    /**
     * Checks if such resource is supported.
     *
     * @param array $resource The source data
     *
     * @return Boolean
     */
    public function supports($resource)
    {
        return is_array($resource);
    }

    /**
     * Loads data.
     *
     * @param array $resource The source data
     *
     * @return mixed
     */
    public function load($resource)
    {
        return $resource;
    }
}
