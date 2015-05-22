<?php

namespace League\Url\Test;

use League\Url\Component;
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
        $user = new Component(new Component($raw));
        $this->assertSame($parsed, $user->getUriComponent());
    }
}
