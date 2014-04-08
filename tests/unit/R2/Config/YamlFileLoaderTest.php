<?php

namespace unit\R2\Config;

use R2\Config\YamlFileLoader;

class YamlFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $sourceFile;

    protected function setUp()
    {
        $this->sourceFile = tempnam(sys_get_temp_dir(), 'Tmp').'.yml';
    }

    protected function tearDown()
    {
        if (file_exists($this->sourceFile)) {
            unlink($this->sourceFile);
        }
    }

    /**
     * @covers R2\Config\YamlFileLoader::supports
     */
    public function testSupports()
    {
        $loader = new YamlFileLoader();
        $this->assertTrue($loader->supports($this->sourceFile));
        $this->assertFalse($loader->supports(['array']));
        $this->assertFalse($loader->supports('/tmp/wrongfilename.tmp'));
    }

    /**
     * @covers R2\Config\YamlFileLoader::load
     */
    public function testLoad()
    {
        $loader = new YamlFileLoader();
        $text = <<<EOT
general:
    alfa: 1
section:
    beta: ololo
    subsection:
        gamma: false
EOT;
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
        file_put_contents($this->sourceFile, $text);
        $this->assertEquals($array, $loader->load($this->sourceFile));
    }
}
