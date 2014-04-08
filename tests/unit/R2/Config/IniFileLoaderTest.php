<?php

namespace unit\R2\Config;

use R2\Config\IniFileLoader;

class IniFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $sourceFile;

    protected function setUp()
    {
        $this->sourceFile = tempnam(sys_get_temp_dir(), 'Tmp').'.ini';
    }

    protected function tearDown()
    {
        if (file_exists($this->sourceFile)) {
            unlink($this->sourceFile);
        }
    }

    /**
     * @covers R2\Config\IniFileLoader::supports
     */
    public function testSupports()
    {
        $loader = new IniFileLoader();
        $this->assertTrue($loader->supports($this->sourceFile));
        $this->assertFalse($loader->supports(['array']));
        $this->assertFalse($loader->supports('/tmp/wrongfilename.tmp'));
    }

    /**
     * @covers R2\Config\IniFileLoader::load
     */
    public function testLoad()
    {
        $loader = new IniFileLoader();
        $text = <<<EOT
[general]
alfa = 1
[section]
beta = ololo
[section.subsection]
gamma = false
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
