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
     * @param $host
     * @param $isIp
     * @param $isIpv4
     * @param $isIpv6
     * @param $uri
     * @dataProvider validHostProvider
     */
    public function testValidHost($host, $isIp, $isIpv4, $isIpv6, $uri)
    {
        $host = new Host($host);
        $this->assertSame($isIp, $host->isIp());
        $this->assertSame($isIpv4, $host->isIpv4());
        $this->assertSame($isIpv6, $host->isIpv6());
        $this->assertSame($uri, $host->getUriComponent());
    }

    public function validHostProvider()
    {
        return [
            'ipv4' => ['127.0.0.1', true, true, false, '127.0.0.1'],
            'naked ipv6' => ['::1', true, false, true, '[::1]'],
            'ipv6' => ['[::1]', true, false, true, '[::1]'],
            'normalized' => ['Master.EXAMPLE.cOm', false, false, false, 'master.example.com'],
            'null' => [null, false, false, false, ''],
            'dot ending' => ['example.com.', false, false, false, 'example.com.'],
            'partial numeric' => ['23.42c.two', false, false, false, '23.42c.two'],
            'all numeric' => ['98.3.2', false, false, false, '98.3.2'],
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
            ['.example.com'],
            ['.......'],
            ['tot.    .coucou.com'],
            ['re view'],
            ['_bad.host.com'],
            [implode('', array_fill(0, 23, 'banana')).'secure.example.com'],
            [implode('.', array_fill(0, 128, 'a'))],
            [implode('.', array_fill(0, 23, 'banana-slip'))],
            ['[127.0.0.1]'],
            ['toto.127.0.0.1'],
            ['[[::1]]'],
        ];
    }

    /**
     * @param $raw
     * @param $expected
     * @dataProvider isAbsoluteProvider
     */
    public function testIsAbsolute($raw, $expected)
    {
        $this->assertSame($expected, (new Host($raw))->isAbsolute());
    }

    public function isAbsoluteProvider()
    {
        return [
            ['127.0.0.1', false],
            ['example.com.', true],
            ['example.com', false],
        ];
    }

    /**
     * Test Punycode support
     *
     * @param $unicode Unicode Hostname
     * @param $ascii   Ascii Hostname
     * @dataProvider hostnamesProvider
     */
    public function testValidUnicodeHost($unicode, $ascii)
    {
        $host = new Host($unicode);
        $this->assertSame($ascii, $host->__toString());
        $this->assertSame($unicode, $host->toUnicode());
    }

    public function hostnamesProvider()
    {
        // http://en.wikipedia.org/wiki/.test_(international_domain_name)#Test_TLDs
        return [
            ['مثال.إختبار', 'xn--mgbh0fb.xn--kgbechtv'],
            ['مثال.آزمایشی', 'xn--mgbh0fb.xn--hgbk6aj7f53bba'],
            ['例子.测试', 'xn--fsqu00a.xn--0zwm56d'],
            ['例子.測試', 'xn--fsqu00a.xn--g6w251d'],
            ['пример.испытание', 'xn--e1afmkfd.xn--80akhbyknj4f'],
            ['उदाहरण.परीक्षा', 'xn--p1b6ci4b4b3a.xn--11b5bs3a9aj6g'],
            ['παράδειγμα.δοκιμή', 'xn--hxajbheg2az3al.xn--jxalpdlp'],
            ['실례.테스트', 'xn--9n2bp8q.xn--9t4b11yi5a'],
            ['בײַשפּיל.טעסט', 'xn--fdbk5d8ap9b8a8d.xn--deba0ad'],
            ['例え.テスト', 'xn--r8jz45g.xn--zckzah'],
            ['உதாரணம்.பரிட்சை', 'xn--zkc6cc5bi7f6e.xn--hlcj6aya9esc7a'],
            ['derhausüberwacher.de', 'xn--derhausberwacher-pzb.de'],
            ['renangonçalves.com', 'xn--renangonalves-pgb.com'],
            ['рф.ru', 'xn--p1ai.ru'],
            ['δοκιμή.gr', 'xn--jxalpdlp.gr'],
            ['ফাহাদ্১৯.বাংলা', 'xn--65bj6btb5gwimc.xn--54b7fta0cc'],
            ['𐌀𐌖𐌋𐌄𐌑𐌉·𐌌𐌄𐌕𐌄𐌋𐌉𐌑.gr', 'xn--uba5533kmaba1adkfh6ch2cg.gr'],
            ['guangdong.广东', 'guangdong.xn--xhq521b'],
            ['gwóźdź.pl', 'xn--gwd-hna98db.pl'],
        ];
    }

    /**
     * Test Punycode support with IP address
     *
     * @param $unicode Unicode Hostname
     * @param $ascii   Ascii Hostname
     * @dataProvider hostnamesIpProvider
     */
    public function testUnicodeWithIP($ip, $res)
    {
        $host = new Host($ip);
        $this->assertSame($host->toUnicode(), $host->__toString());
    }

    public function hostnamesIpProvider()
    {
        return [
            ['::1', '[::1]'],
            ['127.0.0.1', '127.0.0.1'],
        ];
    }

    /**
     * Test Countable
     *
     * @param $host
     * @param $nblabels
     * @param $array
     * @dataProvider countableProvider
     */
    public function testCountable($host, $nblabels, $array)
    {
        $obj = new Host($host);
        $this->assertCount($nblabels, $obj);
        $this->assertSame($array, $obj->toArray());
    }

    public function countableProvider()
    {
        return [
            'ip' => ['127.0.0.1', 1, ['127.0.0.1']],
            'string' => ['secure.example.com', 3, ['secure', 'example', 'com']],
        ];
    }

    /**
     * @param $input
     * @param $is_absolute
     * @param $expected
     * @dataProvider createFromArrayValid
     */
    public function testCreateFromArray($input, $is_absolute, $expected)
    {
        $this->assertSame($expected, Host::createFromArray($input, $is_absolute)->__toString());
    }

    public function createFromArrayValid()
    {
        return [
            'array' => [['www', 'example', 'com'], Host::IS_RELATIVE, 'www.example.com',],
            'iterator' => [new ArrayIterator(['www', 'example', 'com']), Host::IS_RELATIVE, 'www.example.com',],
            'host object' => [new Host('::1'), Host::IS_RELATIVE, '[::1]'],
            'ip 1' => [[127, 0, 0, 1], Host::IS_RELATIVE, '127.0.0.1'],
            'ip 2' => [['127.0', '0.1'], Host::IS_RELATIVE, '127.0.0.1'],
            'ip 3' => [['127.0.0.1'], Host::IS_RELATIVE, '127.0.0.1'],
            'FQDN' => [['www', 'example', 'com'], Host::IS_ABSOLUTE, 'www.example.com.'],
        ];
    }

    /**
     * @param $input
     * @param $is_absolute
     * @dataProvider createFromArrayInvalid
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromArrayFailed($input, $is_absolute)
    {
        Host::createFromArray($input, $is_absolute);
    }

    public function createFromArrayInvalid()
    {
        return [
            'string' => ['www.example.com', Host::IS_RELATIVE],
            'bool' => [true, Host::IS_RELATIVE],
            'integer' => [1, Host::IS_RELATIVE],
            'object' => [new \StdClass(), Host::IS_RELATIVE],
            'ip FQDN' => [['127.0.0.1'], Host::IS_ABSOLUTE],
            'ipv6 FQDN' => [['::1'], Host::IS_ABSOLUTE],
            'unknown flag' => [['all', 'is', 'good'], 23],
        ];
    }

    public function testGetLabel()
    {
        $host = new Host('master.example.com');
        $this->assertSame('master', $host->getLabel(0));
        $this->assertNull($host->getLabel(23));
        $this->assertSame('toto', $host->getLabel(23, 'toto'));
    }

    public function testOffsets()
    {
        $host = new Host('master.example.com');
        $this->assertSame([0, 1, 2], $host->offsets());
        $this->assertSame([1], $host->offsets('example'));
    }

    /**
     * @param $host1
     * @param $host2
     * @param $bool
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
            ['::1', '::1', true],
            ['toto.com', 'barbaz.be', false],
        ];
    }

    /**
     * @param $host
     * @param $without
     * @param $res
     * @dataProvider withoutProvider
     */
    public function testWithout($host, $without, $res)
    {
        $this->assertSame($res, (new Host($host))->without($without)->__toString());
    }

    public function withoutProvider()
    {
        return [
            ['secure.example.com', [0], 'example.com'],
            ['127.0.0.1', [0, 1] , ''],
            ['127.0.0.1', [0], ''],
        ];
    }

    /**
     * @param $raw
     * @param $prepend
     * @param $expected
     * @dataProvider validPrepend
     */
    public function testPrepend($raw, $prepend, $expected)
    {
        $host    = new Host($raw);
        $newHost = $host->prepend($prepend);
        $this->assertSame($expected, $newHost->__toString());
    }

    public function validPrepend()
    {
        return [
            ['secure.example.com', new Host('master'), 'master.secure.example.com'],
            ['secure.example.com', 'master', 'master.secure.example.com'],
            ['secure.example.com', new Host('master.'), 'master.secure.example.com'],
            ['secure.example.com', 'master.', 'master.secure.example.com'],
            ['secure.example.com.', new Host('master'), 'master.secure.example.com.'],
            ['secure.example.com.', 'master', 'master.secure.example.com.'],
        ];
    }

    /**
     * @expectedException LogicException
     */
    public function testPrependIpFailed()
    {
        (new Host('127.0.0.1'))->prepend(new Host('foo'));
    }

    /**
     * @param $raw
     * @param $append
     * @param $expected
     * @dataProvider validAppend
     */
    public function testAppend($raw, $append, $expected)
    {
        $host    = new Host($raw);
        $newHost = $host->append($append);
        $this->assertSame($expected, $newHost->__toString());
    }

    public function validAppend()
    {
        return [
            ['secure.example.com', new Host('master'), 'secure.example.com.master'],
            ['secure.example.com', 'master', 'secure.example.com.master'],
            ['secure.example.com', new Host('master.'), 'secure.example.com.master'],
            ['secure.example.com', 'master.', 'secure.example.com.master'],
            ['secure.example.com.', new Host('master'), 'secure.example.com.master.'],
            ['secure.example.com.', 'master', 'secure.example.com.master.'],
        ];
    }

    /**
     * @expectedException LogicException
     */
    public function testAppendIpFailed()
    {
        (new Host('127.0.0.1'))->append(new Host('foo'));
    }

    /**
     * @param $raw
     * @param $input
     * @param $offset
     * @param $expected
     * @dataProvider replaceValid
     */
    public function testReplace($raw, $input, $offset, $expected)
    {
        $host = new Host($raw);
        $newHost = $host->replace($offset, $input);
        $this->assertSame($expected, $newHost->__toString());
    }

    public function replaceValid()
    {
        return [
            ['master.example.com', new Host('shop'), 0, 'shop.example.com'],
            ['', new Host('::1'), 0, '[::1]'],
            ['toto', new Host('::1'), 23, 'toto'],
            ['master.example.com', 'shop', 0, 'shop.example.com'],
            ['', '::1', 0, '[::1]'],
            ['toto', '::1', 23, 'toto'],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testReplaceIpMustFailed()
    {
        (new Host('secure.example.com'))->replace(2, new Host('127.0.0.1'));
    }
}
