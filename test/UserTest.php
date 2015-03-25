<?php

namespace League\Url\Test;

use League\Url\User;
use PHPUnit_Framework_TestCase;

/**
 * @group components
 */
class UserTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $port = new User(new User('toto'));
        $this->assertSame('toto', $port->get());
    }

    public function testGetUriComponent()
    {
        $port = new User('toto');
        $this->assertSame('toto', $port->getUriComponent());
    }

    public function testGetUriComponentWithEmptyData()
    {
        $port = new User();
        $this->assertSame('', $port->getUriComponent());
    }
}
