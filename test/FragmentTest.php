<?php

namespace League\Url\Test;

use League\Url\Fragment;
use PHPUnit_Framework_TestCase;

/**
 * @group components
 */
class FragmentTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyFragment()
    {
        $fragment = new Fragment();
        $this->assertNull($fragment->get());
    }

    public function testStripDashFragment()
    {
        $fragment = new Fragment('#');
        $this->assertSame('#', $fragment->get());
    }

    public function testgetUriComponentWithEmptyFragment()
    {
        $fragment = new Fragment();
        $this->assertEmpty($fragment->getUriComponent());
    }

    public function testgetUriComponent()
    {
        $fragment = new Fragment('toofan');
        $this->assertSame('#toofan', $fragment->getUriComponent());
    }

    public function testAllowedSymbolsNotEncodedWhenCastToString()
    {
        $expected = "azAZ0-9/?-._~!$&'()*+,;=:@";
        $fragment = new Fragment($expected);

        $this->assertEquals($expected, $fragment->__toString());
    }

    public function testNotAllowedSymbolsEncodedWhenCastToString()
    {
        $fragment = new Fragment("%^[]{}\"<>\\");

        $this->assertEquals("%25%5E%5B%5D%7B%7D%22%3C%3E%5C", $fragment->__toString());
    }
}
