<?php

namespace League\Url\Test\Components;

use ArrayIterator;
use League\Url\Host;
use PHPUnit_Framework_TestCase;

/**
 * @group segment
 */
class HostTest extends PHPUnit_Framework_TestCase
{
    public function testIpv4()
    {
        $host = new Host('127.0.0.1', 'UTF-8');
        $this->assertTrue($host->isIp());
        $this->assertTrue($host->isIpv4());
        $this->assertFalse($host->isIpv6());
        $this->assertSame('127.0.0.1', (string) $host);
        $this->assertSame('127.0.0.1', $host->getUriComponent());
    }

    /**
     * @expectedException InvalidArgumentException
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
        $this->assertSame($expected, (string) $host);
        $this->assertSame('['.$expected.']', $host->getUriComponent());
        $this->assertTrue($host->sameValueAs(new Host('['.$expected.']')));
    }

    /**
     * @expectedException LogicException
     */
    public function testAppendWithIpFailed()
    {
        (new Host('127.0.0.1'))->appendWith('foo');
    }

    /**
     * @expectedException LogicException
     */
    public function testPrependWithIpFailed()
    {
        (new Host('127.0.0.1'))->prependWith('foo');
    }

    public function testRemoveWithWrongValue()
    {
        $host = (new Host('127.0.0.1'))->without('foo');
        $this->assertSame('127.0.0.1', $host->__toString());
    }

    public function testRemoveWhereItMakesNoSense()
    {
        $host = (new Host())->without('foo');
        $this->assertNull($host->get());
    }

    public function testRemoveEmpty()
    {
        $host = (new Host('toto.com'))->without('      ');
        $this->assertSame('toto.com', $host->get());
    }

    public function testPrependWith()
    {
        $host    = new Host('secure.example.com');
        $newHost = $host->prependWith('master');
        $this->assertSame('master.secure.example.com', $newHost->get());
    }

    public function testWithout()
    {
        $host    = new Host('secure.example.com');
        $newHost = $host->without('secure');
        $this->assertSame('example.com', $newHost->get());
    }

    public function testAppendWith()
    {
        $host    = new Host('secure.example.com');
        $newHost = $host->appendWith('shop');
        $this->assertSame('secure.example.com.shop', $newHost->get());
    }

    public function testReplaceWith()
    {
        $host    = new Host('master.example.com');
        $newHost = $host->replaceWith('shop', 0);
        $this->assertSame('shop.example.com', $newHost->get());
    }

    public function testReplaceWithWithIpAddressOnEmptyHost()
    {
        $host = new Host();
        $newHost = $host->replaceWith('::1', 0);
        $this->assertSame('[::1]', $newHost->getUriComponent());
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testReplaceWithFailedWithWrongOffset()
    {
        $host = new Host('toto');
        $host->replaceWith('::1', 23);
    }

    public function testHostSetterWithNull()
    {
        $host = new Host();
        $this->assertNull($host->get());
    }

    public function testGetValue()
    {
        $host = new Host('master.example.com');
        $this->assertSame('master', $host->getValue(0));
        $this->assertNull($host->getValue(23));
        $this->assertSame('toto', $host->getValue(23, 'toto'));
    }

    public function testGetKeys()
    {
        $host = new Host('master.example.com');
        $this->assertSame([0, 1, 2], $host->getKeys());
        $this->assertSame([1], $host->getKeys('example'));
    }

    /**
     * Test Punycode support
     *
     * @param string $unicode Unicode Hostname
     * @param string $ascii   Ascii Hostname
     * @dataProvider hostnamesProvider
     */
    public function testPunycode($unicode, $ascii)
    {
        $host = new Host($unicode);
        $this->assertSame($ascii, $host->toAscii());
        $this->assertSame($unicode, $host->toUnicode());
        $this->assertSame($unicode, $host->__toString());
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
     * @expectedException \InvalidArgumentException
     */
    public function testHostWithMultipleDot()
    {
        new Host('........');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHostWithEmptyLabel()
    {
        new Host('tot.   .coucou.com');
    }

    public function testLegacyHost()
    {
        $host = new Host('tot.coucou.com.');
        $this->assertSame('tot.coucou.com', $host->get());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHostWIthInvalidHostContent()
    {
        new Host('re view');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadHostCharacters()
    {
        new Host('_bad.host.com');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadHostLength()
    {
        $host = new Host('secure.example.com');
        $host->prependWith(implode('', array_fill(0, 23, 'banana')));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testTooManyHostlabel()
    {
        new Host(implode('.', array_fill(0, 128, 'a')));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHostTooLong()
    {
        new Host(implode('.', array_fill(0, 23, 'banana-slip')));
    }

    public function testStrtolowerHost()
    {
        $host = new Host('Master.EXAMPLE.cOm');
        $this->assertSame('master.example.com', (string) $host);
    }
}
