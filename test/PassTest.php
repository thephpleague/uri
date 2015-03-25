<?php

namespace League\Url\Test\Components;

use League\Url\Pass;
use PHPUnit_Framework_TestCase;

/**
 * @group components
 */
class PassTest extends PHPUnit_Framework_TestCase
{
    public function testPassConstructor()
    {
        $port = new Pass(new Pass('toto'));
        $this->assertSame('toto', $port->get());
    }

    public function testGetUriComponent()
    {
        $port = new Pass('toto');
        $this->assertSame(':toto', $port->getUriComponent());
    }

    public function testGetUriComponentWithEmptyPort()
    {
        $port = new Pass();
        $this->assertSame('', $port->getUriComponent());
    }
}
