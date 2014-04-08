<?php

namespace R2\DependencyInjection;

interface ContainerAwareInterface
{
    /**
     * Sets the Container.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer(ContainerInterface $container);
}
