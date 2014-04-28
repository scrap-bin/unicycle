<?php

namespace unit\R2\Templating;

use R2\Validator\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var array */
    protected $rules;
    /** @var array */
    protected $groups;
    /** @var R2\Translator\TranslatorInterface */
    protected $i18n;

    protected function setUp()
    {
        $this->rules = [
            'NotGuest'       => '/^(?!guest$).*$/i',
            'ValidCharsOnly' => '/^[^\[\]\'"@]+$/',
        ];

        $this->groups = [
            'login' => [
                'username' => [ 'NotBlank' => null, 'message' => 'Invalid username' ],
                'password' => [ 'NotBlank' => null, 'message' => 'Invalid password' ],
            ],
            'register' => [
                'username' => [
                    'MinLength' => 2,
                    'MaxLength' => 25,
                    'NotGuest' => null,
                    'ValidCharsOnly' => null,
                    'message' => 'Bad username',
                ],
                'password' => [
                    'MinLength' => 6,
                    'message' => 'Bad password',
                ],
                'password2' => [
                    'TheSame' => 'password',
                    'message' => 'Pass not match',
                ],
                'email' => [
                    'Email' => null,
                    'message' => 'Invalid email',
                ],
            ],
        ];

        $this->i18n = $this->getMock('R2\\Translation\\TranslatorInterface');
        $this->i18n->method('t')->will($this->returnArgument(0));

    }

    /**
     * @covers R2\Validator\Validator::validate
     */
    public function testValidate()
    {
        $validator = new Validator($this->rules, $this->groups, $this->i18n);
        $login = [
            'username' => 'ololo',
            'password' => 'qwerty',
        ];
        $this->assertEquals([], $validator->validate($login, 'login'));
        $register = [
            'username' => 'xyz',
            'password' => 'olololo',
            'password2' => 'lololooo',
            'email' => 'xyy'
        ];
        $this->assertEquals(
            ['password2' => 'Pass not match', 'email' => 'Invalid email'],
            $validator->validate($register, 'register')
        );
    }
}
