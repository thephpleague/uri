<?php

namespace League\Uri\Test\Components;

use InvalidArgumentException;
use League\Uri\Components\User;
use League\Uri\Test\AbstractTestCase;

/**
 * @group user
 */
class UserTest extends AbstractTestCase
{
    /**
     * @supportsDebugInfo
     */
    public function testDebugInfo()
    {
        $component = new User('yolo');
        $this->assertInternalType('array', $component->__debugInfo());
        ob_start();
        var_dump($component);
        $res = ob_get_clean();
        $this->assertContains($component->__toString(), $res);
        $this->assertContains('user', $res);
    }

    /**
     * @dataProvider validUserProvider
     * @param $raw
     * @param $parsed
     */
    public function testGetUriComponent($raw, $parsed)
    {
        $user = new User($raw);
        $this->assertSame($parsed, $user->getUriComponent());
    }

    public function validUserProvider()
    {
        return [
            ['toto', 'toto'],
            ['bar---', 'bar---'],
            ['', '', ''],
            ['"bad"', '%22bad%22'],
            ['<not good>', '%3Cnot%20good%3E'],
            ['{broken}', '%7Bbroken%7D'],
            ['`oops`', '%60oops%60'],
            ['\\slashy', '%5Cslashy'],
        ];
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
            'invalid / character' => ['to/to'],
            'invalid ? character' => ['to?to'],
            'invalid # character' => ['to#to'],
            'array' => [['coucou']],
            'bool'      => [true],
            'Std Class' => [(object) 'foo'],
            'float'     => [1.2],
        ];
    }
}
