<?php

namespace League\Url\Test\Components;

use League\Url\Components\Scheme;
use PHPUnit_Framework_TestCase;

/**
 * @group components
 */
class SchemeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testAccess()
    {
        $scheme = new Scheme();
        $this->assertNull($scheme->get());
        $scheme->set('HTTP');
        $this->assertSame('http', $scheme->get());
        $scheme->set('ftp');
        $this->assertSame('ftp://', $scheme->getUriComponent());
        $scheme->set('svn');
    }

    public function testSameValueAs()
    {
        $scheme = new Scheme();
        $scheme1 = new Scheme('https');
        $this->assertFalse($scheme->sameValueAs($scheme1));
        $scheme1->set(null);
        $this->assertTrue($scheme->sameValueAs($scheme1));
    }
}
