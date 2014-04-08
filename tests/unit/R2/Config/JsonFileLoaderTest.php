<?php

namespace unit\R2\Config;

use R2\Config\JsonFileLoader;

class JsonFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $sourceFile;

    protected function setUp()
    {
        $this->sourceFile = tempnam(sys_get_temp_dir(), 'Tmp').'.json';
    }

    protected function tearDown()
    {
        if (file_exists($this->sourceFile)) {
            unlink($this->sourceFile);
        }
    }

    /**
     * @covers R2\Config\JsonFileLoader::supports
     */
    public function testSupports()
    {
        $loader = new JsonFileLoader();
        $this->assertTrue($loader->supports($this->sourceFile));
        $this->assertFalse($loader->supports(['array']));
        $this->assertFalse($loader->supports('/tmp/wrongfilename.tmp'));
    }

    /**
     * @covers R2\Config\JsonFileLoader::load
     */
    public function testLoad()
    {
        $loader = new JsonFileLoader();
        $array = [
            'general' => [
                'alfa' => 1
            ],
            'section' => [
                'beta' => 'ololo',
                'subsection' => [
                    'gamma' => false
                ]
            ]
        ];
        $text = json_encode($array);
        file_put_contents($this->sourceFile, $text);
        $this->assertEquals($array, $loader->load($this->sourceFile));
    }
}
