<?php

namespace unit\R2\Config;

use R2\Config\PhpFileLoader;

class PhpFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $sourceFile;

    protected function setUp()
    {
        $this->sourceFile = tempnam(sys_get_temp_dir(), 'Tmp').'.php';
    }

    protected function tearDown()
    {
        if (file_exists($this->sourceFile)) {
            unlink($this->sourceFile);
        }
    }

    /**
     * @covers R2\Config\PhpFileLoader::supports
     */
    public function testSupports()
    {
        $loader = new PhpFileLoader();
        $this->assertTrue($loader->supports($this->sourceFile));
        $this->assertFalse($loader->supports(['array']));
        $this->assertFalse($loader->supports('/tmp/wrongfilename.tmp'));
    }

    /**
     * @covers R2\Config\PhpFileLoader::load
     */
    public function testLoad()
    {
        $loader = new PhpFileLoader();
        $text = <<<EOT
<?php
return [
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
