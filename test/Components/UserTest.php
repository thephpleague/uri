<?php

namespace League\Url\test;

use PHPUnit_Framework_TestCase;
use League\Url\Components\User;
use League\Url\Components\Port;

/**
 * @group components
 */
class UserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testExchange()
    {
        $old = new User;
        $new = new User('http');
        $old->exchange($new);
        $this->assertSame('http', $old->get());
        $new->exchange(new Port);
    }
}
