<?php

namespace unit\R2\Application;

use R2\Application\Container;
use R2\Config\CachedFileLoader;
use R2\Config\YamlFileLoader;
use R2\Application\WebApp;

class WebAppTest extends \PHPUnit_Framework_TestCase
{
    /** @var R2\Application\WebApp */
    protected $app;

    protected function setUp()
    {
        $appDir = \realpath(__DIR__.'/../../../../app');
        $container = new Container(
            new CachedFileLoader(new YamlFileLoader(), $appDir.'/cache/%s.ser'),
            $appDir.'/config/config.yml',
            'test'
        );
        $this->app = new WebApp();
        $this->app->setContainer($container);
    }

    protected function tearDown()
    {
        unset($this->app);
    }

    /**
     * @covers R2\Application\WebApp::handleRequest
     */
    public function testHandleRequest()
    {
        $this->expectOutputString('Hello world!');
        $this->app->handleRequest();
    }
}
