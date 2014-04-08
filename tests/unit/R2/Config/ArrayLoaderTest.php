<?php

namespace unit\R2\Config;

use R2\Config\ArrayLoader;

class ArrayLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers R2\Config\ArrayLoader::supports
     */
    public function testSupports()
    {
        $loader = new ArrayLoader();
        $this->assertTrue($loader->supports(['array']));
        $this->assertFalse($loader->supports('string'));
    }

    /**
     * @covers R2\Config\ArrayLoader::load
     */
    public function testLoad()
    {
        $loader = new ArrayLoader();
        $array = ['array'];
        $this->assertEquals($array, $loader->load($array));
    }
}
