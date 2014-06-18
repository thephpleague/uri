<?php

namespace League\Url\test;

use PHPUnit_Framework_TestCase;
use League\Url\Components\Pass;
use League\Url\Components\Port;

/**
 * @group components
 */
class PassTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testExchange()
    {
        $old = new Pass;
        $new = new Pass('http');
        $old->exchange($new);
        $this->assertSame('http', $old->get());
        $new->exchange(new Port);
    }
}
