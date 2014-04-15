<?php

namespace unit\R2\Application;

use R2\Config\ArrayLoader;
use R2\Application\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /** @var R2\Application\Container */
    protected $container;

    protected function setUp()
    {
        $resource = [
            'parameters' => [
                'debug' => true,
                'dbal_class' => 'unit\\R2\\Application\\ServiceMock',
                'db_params' => [
                    'host'        => 'localhost',
                    'username'    => 'xuser',
                    'password'    => 'xpassword',
                    'name'        => 'xname',
                    'dbname'      => 'xdbname',
                    'prefix'      => 'x_',
                    'socket'      => null,
                    'persistent'  => false,
                ],
            ],
            'services' => [
                'my_service' => [
                    'class' => 'unit\\R2\\Application\\ServiceMock',
                    'factory_method' => 'createNewService',
                    'arguments' => ['foo'],
                ],
                'my_shared_service' => [
                    'class' => '%dbal_class%',
                    // defaults: 'factory_method' => 'createSharedService',
                    'arguments' => ['%db_params%'],
                ]
            ]
        ];
        $loader = new ArrayLoader();
        $this->container = new Container($loader, $resource);
    }

    protected function tearDown()
    {
        unset($this->container);
    }

    /**
     * @covers R2\Application\Container::getParameter
     */
    public function testGetParameter()
    {
        // Defined parameter
        $this->assertEquals('array', gettype($this->container->getParameter('parameters.db_params')));
        // Default parameter
        $this->assertEquals(true, $this->container->getParameter('parameters.debug'));
    }

    /**
     * @covers R2\Application\Container::setParameter
     * @covers R2\Application\Container::getParameter
     */
    public function testSetParameter()
    {
        $this->container->setParameter('parameters.another_value', '9000');
        $this->assertEquals('9000', $this->container->getParameter('parameters.another_value'));
    }

    /**
     * @covers R2\Application\Container::get
     * @covers R2\Application\Container::createService
     * @covers R2\Application\Container::createSharedService
     */
    public function testGet()
    {
        $service1 = $this->container->get('my_service');
        $this->assertEquals('unit\\R2\\Application\\ServiceMock', get_class($service1));
        $this->assertEquals('foo', $service1->arg);
        // New instance of the same service
        $service2 = $this->container->get('my_service');
        $this->assertNotSame($service1, $service2);
        // Shared service
        $service3 = $this->container->get('my_shared_service');
        $this->assertEquals('array', gettype($service3->arg));
        $service4 = $this->container->get('my_shared_service');
        $this->assertSame($service3, $service4);
        // Misc
        $this->assertEquals('xuser', $service3->arg['username']);
    }

    /**
     * @covers R2\Application\Container::set
     * @covers R2\Application\Container::get
     */
    public function testSet()
    {
        $service1 = new ServiceMock('trololo');
        $this->container->set('runtime_service', $service1);
        $service2 = $this->container->get('runtime_service');
        $this->assertEquals('trololo', $service2->arg);
        $this->assertSame($service1, $service2);
    }
}
