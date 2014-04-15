<?php

namespace R2\DependencyInjection;

interface ServiceFactoryInterface
{
    /**
     * Create new service instance
     * @param  string $name
     * @param  array  $definition
     * @return mixed
     */
    public function createNewService($name, array $definition);
    /**
     */
    public function createService($name, array $definition);
}
