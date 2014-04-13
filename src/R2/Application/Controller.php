<?php

namespace R2\Application;

use R2\DependencyInjection\ContainerInterface;
use R2\DependencyInjection\ContainerAwareInterface;

class Controller implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * Sets the Container associated with this Controller.
     * Provides a fluent interface.
     *
     * @param ContainerInterface $container
     *
     * @return Controller
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }
}
