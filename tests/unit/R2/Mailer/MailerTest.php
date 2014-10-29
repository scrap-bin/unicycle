<?php

namespace unit\R2\Mailer;

use R2\Mailer\Mailer;

class MailerTest extends \PHPUnit_Framework_TestCase
{
    /** @var R2\Mailer\Mailer */
    protected $maler;

    protected function setUp()
    {
        $this->mailer = new Mailer(
            [
                'transport' => 'dummy',
                'host'      => 'smtp.yandex.ru:465',
                'username'  => 'myuser@yandex.ru',
                'password'  => 'mypassword',
                'use_ssl'   => true,
                'from_name' => 'myuser',
                'from_email'=> 'myuser@yandex.ru',
            ]
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->mailer);
    }

    /**
     * @covers R2\Mailer\Mailer::send
     */
    public function testSend()
    {
        $result = $this->mailer->send('adresee@example.com', 'Test', 'The message body');
        $this->assertTrue($result);
        // It was a dummy send, all message echoed
        $this->expectOutputString(
            'To: adresee@example.com'.PHP_EOL
            .'Subject: Test'.PHP_EOL
            .'From: "myuser" <myuser@yandex.ru>'.PHP_EOL
            .'Date: Thu, 08 May 2014 13:07:27 +0000'.PHP_EOL
            .'MIME-Version: 1.0'.PHP_EOL
            .'Content-transfer-encoding: 8bit'.PHP_EOL
            .'Content-type: text/plain; charset=utf-8'.PHP_EOL
            .'X-Mailer: Unicycle Mailer'.PHP_EOL
            .PHP_EOL
            .'The message body'
        );
    }
}
