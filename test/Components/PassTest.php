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
     * @dataProvider getUriComponentProvider
     * @param $raw
     * @param $parsed
     */
    public function testGetUriComponent($raw, $content, $parsed)
    {
        $pass = new Pass($raw);
        $this->assertSame($content, $pass->getContent());
        $this->assertSame($parsed, $pass->getUriComponent());
    }

    public function getUriComponentProvider()
    {
        return [
            ['toto', 'toto', 'toto'],
            ['bar---', 'bar---', 'bar---'],
            ['', '', ''],
            [null, null, ''],
            ['"bad"', '%22bad%22', '%22bad%22'],
            ['<not good>', '%3Cnot%20good%3E', '%3Cnot%20good%3E'],
            ['{broken}', '%7Bbroken%7D', '%7Bbroken%7D'],
            ['`oops`', '%60oops%60', '%60oops%60'],
            ['\\slashy', '%5Cslashy', '%5Cslashy'],
            ['to%40to', 'to%40to', 'to%40to'],
            ['to:to', 'to:to', 'to:to'],
            ['to%2Fto', 'to%2Fto', 'to%2Fto'],
            ['to%3Fto', 'to%3Fto', 'to%3Fto'],
            ['to%23to', 'to%23to', 'to%23to'],
            ['to%61to', 'to%61to', 'to%61to'],
        ];
    }

    /**
     * @dataProvider geValueProvider
     */
    public function testGetValue($str, $expected)
    {
        $this->assertSame($expected, (new Pass($str))->getDecoded());
    }

    public function geValueProvider()
    {
        return [
            [null, null],
            ['', ''],
            ['0', '0'],
            ['azAZ0-9%2F%3F-._~!$&\'()*+,;=:%40%^%2F[]{}\"<>\\', 'azAZ0-9/?-._~!$&\'()*+,;=:@%^/[]{}\"<>\\'],
            ['€', '€'],
            ['%E2%82%AC', '€'],
            ['frag ment', 'frag ment'],
            ['frag%20ment', 'frag ment'],
            ['frag%2-ment', 'frag%2-ment'],
            ['fr%61gment', 'fr%61gment'],
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
            'bool' => [true],
            'Std Class' => [(object) 'foo'],
            'float' => [1.2],
            'array' => [['foo']],
            'reserved chars' => ['azAZ0-9/?-._~!$&\'()*+,;=:@%^/[]{}\"<>\\'],
        ];
    }
}
