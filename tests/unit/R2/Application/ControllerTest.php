<?php

namespace unit\R2\Application;

use R2\Application\Controller;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $controller;
    protected $reflection;

    public function setUp()
    {
        $this->controller = new Controller();
        $this->reflection = new \ReflectionClass($this->controller);
    }

    protected function tearDown()
    {
        unset($this->reflection);
        unset($this->controller);
    }

    /**
     * @covers R2\Application\Controller::setContainer
     */
    public function testSetContainer()
    {
        $container = $this->getMock('\R2\DependencyInjection\ContainerInterface');
        $container->method('get')->will($this->returnArgument(0));

        $this->controller->setContainer($container);
        // Container is a protected property, so we need some magic
        $property = $this->reflection->getProperty('container');
        $property->setAccessible(true);
        $container2 = $property->getValue($this->controller);
        $this->assertSame($container, $container2);
        $this->assertEquals('foo', $container2->get('foo'));
    }
}
