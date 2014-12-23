<?php

namespace League\Url\Test\Components;

use ArrayIterator;
use League\Url\Components\Host;
use PHPUnit_Framework_TestCase;

/**
 * @group components
 */
class HostTest extends PHPUnit_Framework_TestCase
{

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
        return array(
            array(
                'مثال.إختبار',
                'xn--mgbh0fb.xn--kgbechtv',
            ),
            array(
                '스타벅스코리아.com',
                'xn--oy2b35ckwhba574atvuzkc.com',
            ),
            array(
                'президент.рф',
                'xn--d1abbgf6aiiy.xn--p1ai',
            ),
        );
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
}
