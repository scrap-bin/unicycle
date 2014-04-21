<?php

namespace unit\R2\Translation;

use R2\Translation\Translator;

class TranslatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var Translator */
    protected $i18n;
    protected $base;

    protected function setUp()
    {
        $this->base = [
            'en' => [
                'common' => [
                    'hello_world' => 'Hello World!',
                ],
            ],
            'ru' => [
                'common' => [
                    'hello_world' => 'Привет Мир!',
                ],
            ],
            
        ];
        $loader = $this->getMock('R2\\Translation\\LoaderInterface');
        $loader->method('load')
            ->will(
                $this->returnCallback(
                    function ($locale, $domain) {
                        return isset($this->base[$locale][$domain]) ? $this->base[$locale][$domain] : false;
                    }
                )
            );
        $loader->method('exists')
            ->will(
                $this->returnCallback(
                    function ($locale, $domain) {
                        return isset($this->base[$locale][$domain]);
                    }
                )
            );
        $this->i18n = new Translator($loader, 'en', 'common');
    }

    protected function tearDown()
    {
    }

    /**
     * @covers R2\Translation\Translator::getLocale
     */
    public function testGetLocale()
    {
        $this->assertEquals('en', $this->i18n->getLocale());
    }

    /**
     * @covers R2\Translation\Translator::setLocale
     * @covers R2\Translation\Translator::getLocale
     */
    public function testSetLocale()
    {
        $this->i18n->setLocale('jp_JP');
        $this->assertEquals('jp_JP', $this->i18n->getLocale());
        $this->i18n->setLocale('en');
        $this->assertEquals('en', $this->i18n->getLocale());
    }

    /**
     * @covers R2\Translation\Translator::t
     */
    public function testT()
    {
        $this->i18n->setLocale('jp_JP');
        // Explicit and valid locale and domain
        $this->i18n->setLocale('en');
        $this->assertEquals('Hello World!', $this->i18n->t('hello_world', 'common'));
        $this->i18n->setLocale('ru');
        $this->assertEquals('Привет Мир!', $this->i18n->t('hello_world', 'common'));
        // Domain by default
        $this->i18n->setLocale('en');
        $this->assertEquals('Hello World!', $this->i18n->t('hello_world'));
        // Missing locale, fallback used
        $this->i18n->setLocale('jp');
        $this->assertEquals('Hello World!', $this->i18n->t('hello_world'));
        // Missing translation, the message itself used
        $this->i18n->setLocale('en');
        $this->assertEquals('hello_kitty', $this->i18n->t('hello_kitty'));
        $this->assertEquals('hello_world', $this->i18n->t('hello_world', 'strange'));
    }
}
