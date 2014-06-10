<?php

namespace League\Url\test;

use ArrayIterator;
use PHPUnit_Framework_TestCase;
use League\Url\Components\Host;

class HostTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
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

    public function testHost()
    {
        $host = new Host('secure.example.com');

        $host->prepend('master');
        $this->assertSame('master.secure.example.com', $host->get());

        $host->remove('secure');
        $this->assertSame('master.example.com', $host->get());

        $host->remove('toto');
        $this->assertSame('master.example.com', $host->get());

        $host->append('shop', 'master');
        $this->assertSame('master.shop.example.com', $host->get());

        $host->remove('shop');
        $host->append('master', 'master');
        $host->append('other', 'master', 1);
        $this->assertSame('master.master.other.example.com', $host->get());

        $host->set('.shop.fremium.com');
        $this->assertSame('shop.fremium.com', $host->get());

        $host->set(array('shop', 'premium', 'org'));
        $this->assertSame('shop.premium.org', $host->get());

        $host->set(new ArrayIterator(array('shop', 'premium', 'com')));
        $this->assertSame('shop.premium.com', $host->get());

        $host->prepend('shop');
        $host->prepend('other', 'shop', 1);
        $this->assertSame('shop.other.shop.premium.com', $host->get());

        $host->set(null);
        $this->assertNull($host->get());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testHostStatus()
    {
        $host = new Host;
        $host[] = 're view';
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testBadHostCharacters()
    {
        new Host('_bad.host.com');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testBadHostLength()
    {
        $host = new Host('secure.example.com');
        $host->append(implode('', array_fill(0, 23, 'banana')), 'secure');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testTooManyHostlabel()
    {
        new Host(array_fill(0, 128, 'a'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testHosttooLong()
    {
        new Host(array_fill(0, 23, 'banana-slip'));
    }
}
