<?php

namespace League\Url\Test;

use League\Url\Fragment;
use PHPUnit_Framework_TestCase;

/**
 * @group components
 */
class FragmentTest extends PHPUnit_Framework_TestCase
{

    /**
     * @param $str
     * @param $raw
     * @param $encoded
     * @dataProvider validFragment
     */
    public function testFragment($str, $raw, $encoded)
    {
        $fragment = new Fragment($str);
        $this->assertSame($raw, $fragment->get());
        $this->assertSame($encoded, $fragment->getUriComponent());
    }

    public function validFragment()
    {
        return [
            'empty' => [null, null, ''],
            'hash'  => ['#', '#', '#%23'],
            'toofan' => ['toofan', 'toofan', '#toofan'],
            'notencoded' => ["azAZ0-9/?-._~!$&'()*+,;=:@", 'azAZ0-9/?-._~!$&\'()*+,;=:@', '#azAZ0-9/?-._~!$&\'()*+,;=:@',],
            'encoded' => ["%^[]{}\"<>\\", "%^[]{}\"<>\\", "#%25%5E%5B%5D%7B%7D%22%3C%3E%5C",],
        ];
    }
}
