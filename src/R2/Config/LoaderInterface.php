<?php

namespace R2\Config;

interface LoaderInterface
{
    /**
     * Checks if such source data is supported.
     * 
     * @param  mixed   $resource
     * 
     * @return Boolean
     */
    public function supports($resource);

    /**
     * Loads data.
     * 
     * @param  mixed $resource
     * 
     * @return mixed
     */
    public function load($resource);
}
