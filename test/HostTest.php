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
    /**
     * Test valid Host
     * @param string $host
     * @param bool $isIp
     * @param bool $isIpv4
     * @param bool $isIpv6
     * @param string $str
     * @param string $uri
     * @dataProvider validHostProvider
     */
    public function testValidHost($host, $isIp, $isIpv4, $isIpv6, $str, $uri)
    {
        $host = new Host($host);
        $this->assertSame($isIp, $host->isIp());
        $this->assertSame($isIpv4, $host->isIpv4());
        $this->assertSame($isIpv6, $host->isIpv6());
        $this->assertSame($str, $host->__toString());
        $this->assertSame($uri, $host->getUriComponent());
    }

    public function validHostProvider()
    {
        return [
            ['127.0.0.1', true, true, false, '127.0.0.1', '127.0.0.1'],
            ['FE80:0000:0000:0000:0202:B3FF:FE1E:8329', true, false, true, 'FE80:0000:0000:0000:0202:B3FF:FE1E:8329', '[FE80:0000:0000:0000:0202:B3FF:FE1E:8329]'],
            ['[::1]', true, false, true, '::1', '[::1]'],
            ['Master.EXAMPLE.cOm', false, false, false, 'master.example.com', 'master.example.com'],
            ['example.com.', false, false, false, 'example.com', 'example.com'],
            [null, false, false, false, '', ''],
        ];
    }

    /**
     * @param              string $invalid
     * @dataProvider       invalidHostProvider
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidHost($invalid)
    {
        new Host($invalid);
    }

    public function invalidHostProvider()
    {
        return [
            ['.......'],
            ['tot.    .coucou.com'],
            ['re view'],
            ['_bad.host.com'],
            [implode('', array_fill(0, 23, 'banana')).'secure.example.com'],
            [implode('.', array_fill(0, 128, 'a'))],
            [implode('.', array_fill(0, 23, 'banana-slip'))],
            ['[127.0.0.1]'],
            ['toto.127.0.0.1'],
            ['98.3.2']
        ];
    }

    /**
     * Test Punycode support
     *
     * @param string $unicode Unicode Hostname
     * @param string $ascii   Ascii Hostname
     * @dataProvider hostnamesProvider
     */
    public function testValidUnicodeHost($unicode, $ascii)
    {
        $host = new Host($unicode, 'UTF-8');
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
     * Test Countable
     *
     * @param  string $host
     * @param  int $nblabels
     * @dataProvider countableProvider
     */
    public function testCountable($host, $nblabels)
    {
        $this->assertCount($nblabels, new Host($host));
    }

    public function countableProvider()
    {
        return [
            ['127.0.0.1', 1],
            ['secure.example.com', 3],
        ];
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
     * @param string $host1
     * @param string $host2
     * @param bool $bool
     * @dataProvider sameValueAsProvider
     */
    public function testSameValueAs($host1, $host2, $bool)
    {
        $this->assertSame($bool, (new Host($host1))->sameValueAs(new Host($host2)));
    }

    public function sameValueAsProvider()
    {
        return [
            ['master.example.com', 'MaStEr.ExAMple.CoM', true],
            ['[::1]', '::1', true],
            ['toto.com', 'barbaz.be', false],
        ];
    }

    /**
     * @param string $host
     * @param string $without
     * @param string $res
     * @dataProvider withoutProvider
     */
    public function testWithout($host, $without, $res)
    {
        $this->assertSame($res, (new Host($host))->without($without)->__toString());
    }

    public function withoutProvider()
    {
        return [
            ['secure.example.com', 'secure', 'example.com'],
            ['127.0.0.1', 'foo', '127.0.0.1'],
            ['', 'foo', ''],
            ['toto.com', '    ', 'toto.com'],
        ];
    }

    public function testPrependWith()
    {
        $host    = new Host('secure.example.com');
        $newHost = $host->prependWith('master');
        $this->assertSame('master.secure.example.com', $newHost->get());
    }

    /**
     * @expectedException LogicException
     */
    public function testPrependWithIpFailed()
    {
        (new Host('127.0.0.1'))->prependWith('foo');
    }

    public function testAppendWith()
    {
        $host    = new Host('secure.example.com');
        $newHost = $host->appendWith('shop');
        $this->assertSame('secure.example.com.shop', $newHost->get());
    }

    /**
     * @expectedException LogicException
     */
    public function testAppendWithIpFailed()
    {
        (new Host('127.0.0.1'))->appendWith('foo');
    }

    public function testReplaceWith()
    {
        $host    = new Host('master.example.com');
        $newHost = $host->replaceWith('shop', 0);
        $this->assertSame('shop.example.com', $newHost->get());
    }

    public function testReplaceWithWithIpAddressOnEmptyHost()
    {
        $host    = new Host();
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

    /**
     * @expectedException InvalidArgumentException
     */
    public function testReplaceWithIpMustFailed()
    {
        $host = new Host('secure.example.com');
        $host->replaceWith('127.0.0.1', 2);
    }
}
