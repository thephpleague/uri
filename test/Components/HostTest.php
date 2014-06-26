<?php

namespace League\Url\test;

use ArrayIterator;
use PHPUnit_Framework_TestCase;
use League\Url\Components\Host;

/**
 * @group components
 */
class HostTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException RuntimeException
     */
    public function testArrayAccess()
    {
        $host = new Host;
        $host[] = 'leheros';
        $this->assertNull($host[5]);
        $this->assertSame('leheros', $host[0]);
        $this->assertSame('leheros', (string) $host);
        $host[0] = 'levilain';
        $host[1] = 'bar';
        $this->assertTrue(isset($host[1]));
        $this->assertCount(2, $host);
        $this->assertSame('levilain.bar', (string) $host);
        foreach ($host as $offset => $value) {
            $this->assertSame($value, $host[$offset]);
        }
        unset($host[0]);
        $this->assertNull($host[0]);
        $this->assertSame(array(1 => 'bar'), $host->toArray());
        $host['toto'] = 'comment ça va';
    }

    public function testHostPrepend()
    {
        $host = new Host('secure.example.com');

        $host->prepend('master');
        $this->assertSame('master.secure.example.com', $host->get());
    }

    public function testHostRemove()
    {
        $host = new Host('secure.example.com');
        $host->remove('secure');
        $this->assertSame('example.com', $host->get());
    }

    public function testHostAppend()
    {
        $host = new Host('secure.example.com');
        $host->append('shop', 'secure');
        $this->assertSame('secure.shop.example.com', $host->get());
    }

    public function testHostAppendWhence()
    {
        $host = new Host('master.example.com');
        $host->append('master', 'master');
        $host->append('other', 'master', 1);
        $this->assertSame('master.master.other.example.com', $host->get());
    }

    public function testHostSetterWithString()
    {
        $host = new Host('master.example.com');
        $host->set('.shop.fremium.com');
        $this->assertSame('shop.fremium.com', $host->get());
    }

    public function testHostSetterWithArray()
    {
        $host = new Host('master.example.com');
        $host->set(array('shop', 'premium', 'org'));
        $this->assertSame('shop.premium.org', $host->get());
    }

    public function testHostSetterWithArrayIterator()
    {
        $host = new Host('master.example.com');
        $host->set(new ArrayIterator(array('shop', 'premium', 'com')));
        $this->assertSame('shop.premium.com', $host->get());
    }

    public function testHostPrependWhence()
    {
        $host = new Host('master.example.com');
        $host->prepend('shop');
        $host->prepend('other', 'shop', 1);
        $this->assertSame('other.shop.master.example.com', $host->get());
    }

    public function testHostSetterWithNull()
    {
        $host = new Host('master.example.com');
        $host->set(null);
        $this->assertNull($host->get());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testHostStatus()
    {
        $host = new Host;
        $host[] = 're view';
    }

    /**
     * @expectedException RuntimeException
     */
    public function testBadHostCharacters()
    {
        new Host('_bad.host.com');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testBadHostLength()
    {
        $host = new Host('secure.example.com');
        $host->append(implode('', array_fill(0, 23, 'banana')), 'secure');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testTooManyHostlabel()
    {
        new Host(array_fill(0, 128, 'a'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testHosttooLong()
    {
        new Host(array_fill(0, 23, 'banana-slip'));
    }
}
