<?php

namespace League\Uri\Test\Components;

use InvalidArgumentException;
use League\Uri\Components\Fragment;
use PHPUnit_Framework_TestCase;

/**
 * @group fragment
 */
class FragmentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validFragment
     * @param $str
     * @param $encoded
     */
    public function testFragment($str, $encoded)
    {
        $fragment = new Fragment($str);
        $this->assertSame($encoded, $fragment->getUriComponent());
    }

    public function validFragment()
    {
        return [
            'null' => [null, ''],
            'empty' => ['', ''],
            'evaluate empty' => ['0', '#0'],
            'hash'  => ['#', '#%23'],
            'toofan' => ['toofan', '#toofan'],
            'notencoded' => ["azAZ0-9/?-._~!$&'()*+,;=:@", '#azAZ0-9/?-._~!$&\'()*+,;=:@'],
            'encoded' => ['%^[]{}"<>\\', '#%25%5E%5B%5D%7B%7D%22%3C%3E%5C'],
        ];
    }

    /**
     * @param $str
     * @expectedException InvalidArgumentException
     * @dataProvider failedConstructor
     */
    public function testInvalidFragment($str)
    {
        new Fragment($str);
    }

    public function failedConstructor()
    {
        return [
            'bool'      => [true],
            'Std Class' => [(object) 'foo'],
            'float'     => [1.2],
            'array'     => [['foo']],
        ];
    }
}
