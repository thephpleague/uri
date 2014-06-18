<?php

namespace League\Url\test;

use PHPUnit_Framework_TestCase;
use League\Url\Components\Scheme;
use League\Url\Components\Port;

/**
 * @group components
 */
class SchemeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testAccess()
    {
        $scheme = new Scheme;
        $this->assertNull($scheme->get());
        $scheme->set('HTTP');
        $this->assertSame('http', $scheme->get());
        $scheme->set('ftp');
        $this->assertSame('ftp://', $scheme->getUriComponent());
        $scheme->set('svn');
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testExchange()
    {
        $old = new Scheme;
        $new = new Scheme('http');
        $old->exchange($new);
        $this->assertSame('http', $old->get());
        $new->exchange(new Port);
    }
}
