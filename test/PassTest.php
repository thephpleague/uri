<?php

namespace League\Uri\Test;

use League\Uri\Pass;
use PHPUnit_Framework_TestCase;

/**
 * @group pass
 */
class PassTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param  string $value
     * @dataProvider validUserProvider
     */
    public function testGetUriComponent($raw, $parsed)
    {
        $this->assertSame($parsed, (new Pass($raw))->getUriComponent());
    }

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
            'invalid character' => ['to@to'],
            'bool'      => [true],
            'Std Class' => [(object) 'foo'],
            'null'      => [null],
            'float'     => [1.2],
            'array'      =>[['foo']],
        ];
    }
}
