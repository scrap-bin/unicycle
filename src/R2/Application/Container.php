<?php

namespace R2\Application;

use R2\DependencyInjection\ContainerInterface;
use R2\DependencyInjection\ServiceFactoryInterface;
use R2\Config\FileLoaderInterface;
use InvalidArgumentException as ArgsException;

/**
 * Application-specific container
 * Supports nested files and some default values
 */
class Container implements ContainerInterface, ServiceFactoryInterface
{
    /** @var array */
    private $shared;
    /** @var R2\Config\LoaderInterface[] */
    private $loaders;
    /** @var array */
    private $properties;

    /**
     * Constructor
     * @param mixed       $loader      One loader object or array of loaders
     * @param mixed       $resource    Resource is something that loader can load. filename as general
     * @param string|null $environment Optional environment parameter. If not specified, mean "production".
     */
    public function __construct($loader, $resource, $environment = null)
    {
        $this->shared = [];
        $this->loaders = is_array($loader) ? $loader : [$loader];
        $loader = $this->resolveLoader($resource);
        $dir = ($loader instanceof FileLoaderInterface) ? dirname($resource) : '.';

        // Default properties
        $this->properties = [
            'parameters' => [
                'debug'       => false,
                'environment' => $environment ?: 'production',
                'root_dir'    => $dir,
            ],
            'synonyms'   => [],
            'services'   => [],
            'security'   => [
                'firewalls'      => [],
                'access_control' => [],
            ],
        ];

        $parameters = $loader->load($resource);
        while (array_key_exists('imports', $parameters)) {
            $imports = $parameters['imports'];
            unset($parameters['imports']);
            foreach ($imports as $p) {
                $path = $dir.'/'.$this->resolve($p);
                $loader = $this->resolveLoader($resource);
                $parameters = array_replace_recursive($loader->load($path), $parameters);
            }
        }
        $this->properties = array_replace_recursive(
            $this->properties,
            $parameters
        );
    }

    /**
     * Returns a loader able to load the resource.
     *
     * @param mixed $resource A resource
     *
     * @return LoaderInterface A LoaderInterface instance
     *
     * @throws \InvalidArgumentException When no proper loader
     */
    private function resolveLoader($resource)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($resource)) {
                return $loader;
            }
        }

        throw new \InvalidArgumentException('Cannot resolve loader');
    }

    /**
     * Get service
     * @param  string $name
     * @return object
     */
    public function get($name)
    {

        if (array_key_exists($name, $this->shared)) {
            return $this->shared[$name];
        }

        $config = $this->properties['services'][$name];
        $definition = [
            'class'           => isset($config['class'])
                               ? $this->resolve($config['class'])
                               : null,
            'factory_service' => isset($config['factory_service'])
                               ? $this->resolve($config['factory_service'])
                               : $this,
            'factory_method'  => isset($config['factory_method'])
                               ? $this->resolve($config['factory_method'])
                               : 'createSharedService',
            'service'         => isset($config['service'])
                               ? $this->resolve($config['service'])
                               : $this,
            'method'          => isset($config['method'])
                               ? $this->resolve($config['method'])
                               : null,
            'arguments'       => isset($config['arguments'])
                               ? $this->resolve($config['arguments'])
                               : [],
            'tags'            => isset($config['tags'])
                               ? $this->resolve($config['tags'])
                               : [],
            'settings'        => isset($config['settings'])
                               ? $this->resolve($config['settings'])
                               : [],
        ];

        if (isset($definition['method'])) {
            return \call_user_func_array(
                [$definition['factory_service'], $definition['method']],
                $definition['arguments']
            );
        } else {
            return $definition['factory_service']->$definition['factory_method']($name, $definition);
        }
    }

    /**
     * Set service
     * @param  string    $name
     * @param  object    $value
     * @return Container
     */
    public function set($name, $value)
    {
        $this->shared[$name] = $value;

        return $this;
    }

    /**
     * Get configuration parameter
     * Use dots to access nested item. For ex. "services.foo.tags"
     * @param  string        $name
     * @return mixed
     * @throws ArgsException
     */
    public function getParameter($name)
    {
        $segments = explode('.', $name);
        $ptr =& $this->properties;
        foreach ($segments as $s) {
            if (!array_key_exists($s, $ptr)) {
                throw new ArgsException("Missing \"{$s}\" in the path \"{$name}\"");
            }
            $ptr =& $ptr[$s];
        }

        return $this->resolve($ptr);
    }

    /**
     * Set configuration parameter
     * @param  string    $name
     * @param  mixed     $value
     * @return Container
     */
    public function setParameter($name, $value)
    {
        $segments = explode('.', $name);
        $n = count($segments);
        $ptr =& $this->properties;
        foreach ($segments as $s) {
            if (--$n) {
                if (!array_key_exists($s, $ptr)) {
                    $ptr[$s] = [];
                } elseif (!is_array($ptr[$s])) {
                    throw new ArgsException("Scalar \"{$s}\" in the path \"{$name}\"");
                }
                $ptr =& $ptr[$s];
            } else {
                $ptr[$s] = $value;
            }
        }

        return $this;
    }

    /**
     * Resolve some special cases:
     *   "@name" is a Service reference
     *   "%name%" is a Parameter reference. looks in "parameters" section
     * @param  mixed $value
     * @return mixed
     */
    private function resolve($value)
    {
        $matches = null;
        if (is_string($value) && strpos($value, '%') !== false) {
            if (preg_match('~^%([a-z_0-9.]+)%$~', $value, $matches)) {
                return $this->substitute($matches);
            } else {
                $value = preg_replace_callback('~%([a-z_0-9.]+)%~', [$this, 'substitute'], $value);
            }
        } elseif (is_string($value) && strpos($value, '@') === 0) {
            return $this->get(substr($value, 1));
        } elseif (is_array($value)) {
            foreach ($value as &$v) {
                $v = $this->resolve($v);
            }
        }

        return $value;
    }

    /**
     * Replace %name% pattern to concrete value
     * @param  array  $matches
     * @return string
     */
    private function substitute($matches)
    {
        if (array_key_exists($matches[1], $this->properties['parameters'])) {
            return $this->properties['parameters'][$matches[1]];
        }
        $name = $this->properties['synonyms'][$matches[1]];

        return $this->getParameter($name);
    }

    /**
     * The default case of creational method (for service factory)
     * Produces new instance in every call
     * @param  string        $name
     * @param  array         $definition
     * @return object
     * @throws ArgsException
     */
    public function createService($name, $definition)
    {
        $class = $definition['class'];
        if (!isset($class)) {
            throw new ArgsException("Class name required");
        }
        $args = $definition['arguments'];
        switch (count($args)) {
            case 0:
                $service = new $class();
                break;
            case 1:
                $service = new $class($args[0]);
                break;
            case 2:
                $service = new $class($args[0], $args[1]);
                break;
            case 3:
                $service = new $class($args[0], $args[1], $args[2]);
                break;
            default:
                $r = new \ReflectionClass($class);
                $service = $r->newInstanceArgs($args);
        }

        if (!empty($definition['settings'])) {
            foreach ($definition['settings'] as $method => $setArgs) {
                call_user_func_array([$service, $method], $setArgs);
            }
        }

        return $service;
    }

    /**
     * The case of creational method (for service factory)
     * Produces single instance
     * @param  string        $name
     * @param  array         $definition
     * @return object
     * @throws ArgsException
     */
    public function createSharedService($name, $definition)
    {
        return $this->shared[$name] = $this->createService($name, $definition);
    }
}
