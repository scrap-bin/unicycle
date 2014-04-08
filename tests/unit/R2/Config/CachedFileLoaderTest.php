<?php

namespace unit\R2\Config;

use R2\Config\YamlFileLoader;
use R2\Config\CachedFileLoader;

class CachedFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $sourceFile;
    protected $template;
    protected $cacheFile;

    protected function setUp()
    {
        $tempDir = sys_get_temp_dir();
        $this->sourceFile = tempnam($tempDir, 'Tmp').'.yml';
        $this->template = $tempDir.'/%s.ser';
        $this->cacheFile  = sprintf($this->template, md5($this->sourceFile));
    }

    protected function tearDown()
    {
        if (file_exists($this->sourceFile)) {
            unlink($this->sourceFile);
        }
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }

    /**
     * @covers R2\Config\CachedFileLoader::supports
     */
    public function testSupports()
    {
        $loader0 = new YamlFileLoader();
        $loader = new CachedFileLoader($loader0, $this->template);
        $this->assertTrue($loader->supports($this->sourceFile));
        $this->assertFalse($loader->supports(['array']));
        $this->assertFalse($loader->supports('/tmp/wrongfilename.tmp'));
    }

    /**
     * @covers R2\Config\CachedFileLoader::load
     */
    public function testLoad()
    {
        $loader0 = new YamlFileLoader();
        $loader = new CachedFileLoader($loader0, $this->template);
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

        // First time file read
        file_put_contents($this->sourceFile, $text);
        $this->assertEquals($array, $loader->load($this->sourceFile));

        // Source is newer than cache
        sleep(1); // let filemtime differs at least on 1 sec
        $text2 = $text."\nsection2:\n    theta: true\n";
        file_put_contents($this->sourceFile, $text2);
        $this->assertNotEquals($array, $loader->load($this->sourceFile));
        $array += ['section2' => ['theta' => true]];
        $this->assertEquals($array, $loader->load($this->sourceFile));

        // Cache is newer than source
        file_put_contents($this->sourceFile, $text);
        sleep(1); // let filemtime differs at least on 1 sec
        touch($this->cacheFile);
        $this->assertEquals($array, $loader->load($this->sourceFile));
    }
}
