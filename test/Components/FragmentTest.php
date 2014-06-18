<?php

namespace League\Url\test;

use PHPUnit_Framework_TestCase;
use League\Url\Components\Fragment;
use League\Url\Components\Port;

/**
 * @group components
 */
class FragmentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testExchange()
    {
        $old = new Fragment;
        $new = new Fragment('http');
        $old->exchange($new);
        $this->assertSame('http', $old->get());
        $new->exchange(new Port);
    }
}
