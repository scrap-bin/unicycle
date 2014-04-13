<?php

namespace R2\Application;

use R2\DependencyInjection\ContainerInterface;
use R2\DependencyInjection\ContainerAwareInterface;

class CliApp implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;
    /** @var R2\ORM\EntityManagerInterface */
    protected $em;
    /** @var R2\Dbal\DbalInterface */
    protected $db;
    /** @var array */
    protected $options;
    /** @var array */
    protected $arguments;

    public function __construct(array $options = [], array $arguments = [])
    {
        $this->options   = $options;
        $this->arguments = $arguments;
    }

    /**
     * Sets the Container associated with this Application.
     * Provides a fluent interface.
     *
     * @param ContainerInterface $container
     *
     * @return CliApp
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $container->get('db');
        $this->em = $container->get('entity_manager');

        return $this;
    }
}
