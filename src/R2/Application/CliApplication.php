<?php

namespace R2\Application;

use R2\DependencyInjection\ContainerInterface;
use R2\DependencyInjection\ContainerAwareInterface;

class CliApplication implements ContainerAwareInterface
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
    /** @var Boolean */
    protected $debug;

    public function __construct(array $options = [], array $arguments = [])
    {
        $this->options   = $options;
        $this->arguments = $arguments;
        set_error_handler([$this, 'onError']);
    }

    public function __destruct()
    {
        restore_error_handler();
    }
    
    public function onError($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
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
        $this->debug = $container->getParameter('parameters.debug');
        if ($this->debug) {
            error_reporting(-1);
        } else {
            error_reporting(0);
        }
        $this->container = $container;
        $this->db = $container->get('db');
        $this->em = $container->get('entity_manager');

        return $this;
    }
}
