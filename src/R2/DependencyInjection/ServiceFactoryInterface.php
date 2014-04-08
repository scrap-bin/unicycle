<?php

namespace R2\DependencyInjection;

interface ServiceFactoryInterface
{
    /**
     * Create new service
     * @param  string $name
     * @param  array  $definition
     * @return mixed
     */
    public function createService($name, $definition);
    /**
     * Create new [singleton] service
     * @param  string $name
     * @param  array  $definition
     * @return mixed
     */
    public function createSharedService($name, $definition);
}
