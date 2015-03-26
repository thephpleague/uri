<?php

namespace League\Url\Test;

use League\Url\Scheme;
use PHPUnit_Framework_TestCase;

/**
 * @group components
 */
class SchemeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAccess()
    {
        $scheme = new Scheme();
        $this->assertNull($scheme->get());
        $http_scheme = $scheme->withValue('HTTP');
        $this->assertSame('http', $http_scheme->get());
        $new_scheme = $scheme->withValue('ftp');
        $this->assertSame('ftp:', $new_scheme->getUriComponent());
        $scheme->withValue('123');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCharacterRange()
    {
        $scheme = new Scheme('in,valid');
    }

    public function testSameValueAs()
    {
        $scheme  = new Scheme();
        $scheme1 = new Scheme('https');
        $this->assertFalse($scheme->sameValueAs($scheme1));
        $newscheme = $scheme1->withValue(null);
        $this->assertTrue($scheme->sameValueAs($newscheme));
        $this->assertSame('', $newscheme->getUriComponent());
    }
}
