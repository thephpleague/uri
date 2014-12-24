<?php

namespace League\Url\Test\Components;

use ArrayIterator;
use League\Url\Host;
use PHPUnit_Framework_TestCase;

/**
 * @group components
 */
class HostTest extends PHPUnit_Framework_TestCase
{

    public function testIpv4()
    {
        $host = new Host('127.0.0.1');
        $this->assertTrue($host->isIp());
        $this->assertTrue($host->isIpv4());
        $this->assertFalse($host->isIpv6());
        $this->assertSame(array(0 => '127.0.0.1'), $host->toArray());
        $this->assertSame('127.0.0.1', (string) $host);
        $this->assertSame('127.0.0.1', $host->getUriComponent());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testIpv6failed()
    {
        new Host('[127.0.0.1]');
    }

    public function testIpv6()
    {
        $expected = 'FE80:0000:0000:0000:0202:B3FF:FE1E:8329';
        $host = new Host($expected);
        $this->assertTrue($host->isIp());
        $this->assertFalse($host->isIpv4());
        $this->assertTrue($host->isIpv6());
        $this->assertSame(array(0 => $expected), $host->toArray());
        $this->assertSame($expected, (string) $host);
        $this->assertSame('['.$expected.']', $host->getUriComponent());
        $this->assertTrue($host->sameValueAs(new Host('['.$expected.']')));
    }

    /**
     * @expectedException LogicException
     */
    public function testAppendWithIpFailed()
    {
        $host = new Host('127.0.0.1');
        $host->append('foo');
    }

    /**
     * @expectedException LogicException
     */
    public function testPrependWithIpFailed()
    {
        $host = new Host('127.0.0.1');
        $host->prepend('foo');
    }

    public function testRemoveWithIpFailed()
    {
        $host = new Host('127.0.0.1');
        $this->assertFalse($host->remove('foo'));
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
        $host->set(new ArrayIterator(['shop', 'premium', 'com']));
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
     * Test Punycode support
     *
     * @param string $idna_unicode Unicode Hostname
     * @param string $idna_ascii   Ascii Hostname
     * @dataProvider hostnamesProvider
     */
    public function testPunycode($idna_unicode, $idna_ascii)
    {
        $host = new Host($idna_unicode);
        $this->assertSame(explode('.', $idna_unicode), $host->toArray());
        $this->assertSame($idna_ascii, $host->toAscii());
        $this->assertSame($idna_unicode, $host->toUnicode());
        $this->assertSame($idna_unicode, (string) $host);
    }

    public function hostnamesProvider()
    {
        return [
            ['مثال.إختبار', 'xn--mgbh0fb.xn--kgbechtv',],
            ['스타벅스코리아.com', 'xn--oy2b35ckwhba574atvuzkc.com',],
            ['президент.рф', 'xn--d1abbgf6aiiy.xn--p1ai',],
        ];
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testHostStatus()
    {
        $host = new Host();
        $host->set('re view');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testBadHostCharacters()
    {
        new Host('_bad.host.com');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testBadHostLength()
    {
        $host = new Host('secure.example.com');
        $host->append(implode('', array_fill(0, 23, 'banana')), 'secure');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testTooManyHostlabel()
    {
        new Host(array_fill(0, 128, 'a'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testHosttooLong()
    {
        new Host(array_fill(0, 23, 'banana-slip'));
    }

    public function testGetSegment()
    {
        $host = new Host('master.example.com');
        $this->assertSame('master', $host->getSegment(0));
        $this->assertNull($host->getSegment(23));
        $this->assertSame('toto', $host->getSegment(23, 'toto'));
    }

    public function testSetSegment()
    {
        $host = new Host('master.example.com');
        $host->setSegment(0, 'slave');
        $this->assertSame('slave', $host->getSegment(0));
        $this->assertSame('slave.example.com', (string) $host);
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testSetSegmentInvalidOffset()
    {
        $host = new Host('master.example.com');
        $host->setSegment(4, 'foo');
    }

    public function testSetSegmentRemoveOffsetWithNullAndEmptyValue()
    {
        $host = new Host('master.example.com');
        $host->setSegment(0, null);
        $this->assertSame('example.com', (string) $host);
        $host->setSegment(0, '');
        $this->assertSame('com', (string) $host);
    }
}
