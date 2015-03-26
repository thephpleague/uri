<?php

namespace League\Url\Test;

use League\Url\User;
use PHPUnit_Framework_TestCase;

/**
 * @group components
 */
class UserTest extends PHPUnit_Framework_TestCase
{
    public function validUserProvider()
    {
        return [
            ['toto', 'toto'],
            ['bar---', 'bar---'],
            [null, ''],
        ];
    }

    /**
     * @param  string $value
     * @dataProvider validUserProvider
     */
    public function testGetUriComponent($raw, $parsed)
    {
        $user = new User(new User($raw));
        $this->assertSame($parsed, $user->getUriComponent());
    }

    public function providerInvalidCharacters()
    {
        return [
            ['"bad"'],
            ['<not good>'],
            ['{broken}'],
            ['failure?'],
            ['`oops`'],
            ['\\slashy'],
        ];
    }

    /**
     * @dataProvider providerInvalidCharacters
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidCharacters($value)
    {
        $user = new User($value);
    }
}
