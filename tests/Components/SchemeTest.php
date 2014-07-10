<?php

namespace League\Url\Test\Components;

use PHPUnit_Framework_TestCase;
use League\Url\Components\Scheme;

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
}
