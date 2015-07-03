<?php

namespace League\Uri\Test;

use League\Uri\User;
use PHPUnit_Framework_TestCase;

/**
 * @group user
 */
class UserTest extends PHPUnit_Framework_TestCase
{
    public function validUserProvider()
    {
        return [
            ['toto', 'toto'],
            ['bar---', 'bar---'],
            ['', ''],
            ['"bad"', "%22bad%22"],
            ['<not good>', "%3Cnot%20good%3E"],
            ['{broken}', '%7Bbroken%7D'],
            ['failure?', 'failure%3F'],
            ['`oops`', '%60oops%60'],
            ['\\slashy', "%5Cslashy"],
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

    /**
     * @param $raw
     * @dataProvider invalidDataProvider
     * @expectedException InvalidArgumentException
     */
    public function testFailedConstructor($raw)
    {
        new User($raw);
    }

    public function invalidDataProvider()
    {
        return [
            'invalid @ character' => ['to@to'],
            'invalid : character' => ['to:to'],
            'array' => [['coucou']],
            'bool'      => [true],
            'Std Class' => [(object) 'foo'],
            'null'      => [null],
            'float'     => [1.2],
        ];
    }
}
