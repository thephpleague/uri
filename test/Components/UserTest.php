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
     */
    public function testGetUriComponent($raw, $parsed)
    {
        $user = new User($raw);
        $this->assertSame($raw, $user->getContent());
        $this->assertSame($parsed, $user->getUriComponent());
    }

    public function validUserProvider()
    {
        return [
            ['toto', 'toto'],
            ['bar---',  'bar---'],
            ['', ''],
            [null, ''],
            ['"bad"', '%22bad%22'],
            ['<not good>', '%3Cnot%20good%3E'],
            ['{broken}', '%7Bbroken%7D'],
            ['`oops`', '%60oops%60'],
            ['\\slashy', '%5Cslashy'],
            ['to@to', 'to%40to'],
            ['to:to', 'to%3Ato'],
            ['to/to', 'to%2Fto'],
            ['to?to', 'to%3Fto'],
            ['to#to', 'to%23to'],
            ['to%61to', 'to%61to'],
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
            'array' => [['coucou']],
            'bool'      => [true],
            'Std Class' => [(object) 'foo'],
            'float'     => [1.2],
        ];
    }
}
