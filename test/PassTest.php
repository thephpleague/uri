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
        $pass = new Pass(new Pass('toto'));
        $this->assertSame('toto', $pass->__toString());
    }

    public function testGetUriComponent()
    {
        $this->assertSame(':toto', (new Pass('toto'))->getUriComponent());
    }

    public function testGetUriComponentWithEmptyPort()
    {
        $this->assertSame('', (new Pass())->getUriComponent());
    }
}
