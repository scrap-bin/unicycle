<?php

namespace R2\DependencyInjection;

interface ContainerInterface
{
    /**
     * Gets a service.
     *
     * @param string $id The service identifier
     *
     * @return object The associated service
     */
    public function get($id);
    /**
     * Sets a service.
     * Provides a fluent interface.
     *
     * @param string $id      The service identifier
     * @param object $service The service instance
     *
     * @return object Self reference
     */
    public function set($id, $service);
    /**
     * Gets a parameter.
     *
     * @param string $name The parameter name
     *
     * @return mixed The parameter value
     */
    public function getParameter($name);
    /**
     * Sets a parameter.
     * Provides a fluent interface.
     *
     * @param string $name  The parameter name
     * @param mixed  $value The parameter value
     *
     * @return object Self reference
     */
    public function setParameter($name, $value);
}
