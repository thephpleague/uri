<?php

namespace League\Url\Test\Components;

use League\Url\Components\Fragment;
use PHPUnit_Framework_TestCase;

/**
 * @group components
 */
class FragmentTest extends PHPUnit_Framework_TestCase
{
    public function testAllowedSymbolsNotEncodedWhenCastToString()
    {
        $fragment = new Fragment("azAZ0-9/?-._~!$&'()*+,;=:@");

        $this->assertEquals("azAZ0-9/?-._~!$&'()*+,;=:@", (string) $fragment);
    }

    public function testNotAllowedSymbolsEncodedWhenCastToString()
    {
        $fragment = new Fragment("#%^[]{}\"<>\\");

        $this->assertEquals("%23%25%5E%5B%5D%7B%7D%22%3C%3E%5C", (string) $fragment);
    }
}
