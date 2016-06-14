<?php

namespace League\Uri\Test\Components;

use InvalidArgumentException;
use League\Uri\Components\Pass;
use League\Uri\Test\AbstractTestCase;

/**
 * @group pass
 */
class PassTest extends AbstractTestCase
{
    /**
     * @supportsDebugInfo
     */
    public function testDebugInfo()
    {
        $component = new Pass('yolo');
        $this->assertInternalType('array', $component->__debugInfo());
        ob_start();
        var_dump($component);
        $res = ob_get_clean();
        $this->assertContains($component->__toString(), $res);
        $this->assertContains('pass', $res);
    }

    /**
     * @dataProvider validUserProvider
     * @param $raw
     * @param $parsed
     */
    public function testGetUriComponent($raw, $parsed)
    {
        $pass = new Pass($raw);
        $this->assertSame($raw, $pass->getContent());
        $this->assertSame($parsed, $pass->getUriComponent());
    }

    public function validUserProvider()
    {
        return [
            ['toto', 'toto'],
            ['bar---', 'bar---'],
            ['', ''],
            ['"bad"', '%22bad%22'],
            ['<not good>', '%3Cnot%20good%3E'],
            ['{broken}', '%7Bbroken%7D'],
            ['`oops`', '%60oops%60'],
            ['\\slashy', '%5Cslashy'],
            ['to@to', 'to%40to'],
            ['to:to', 'to:to'],
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
        new Pass($raw);
    }

    public function invalidDataProvider()
    {
        return [
            'bool'      => [true],
            'Std Class' => [(object) 'foo'],
            'float'     => [1.2],
            'array'      => [['foo']],
        ];
    }
}
