<?php

namespace League\Uri\Test\Components;

use ArrayIterator;
use InvalidArgumentException;
use League\Uri\Components\Host;
use League\Uri\Test\AbstractTestCase;
use LogicException;

/**
 * @group host
 */
class HostTest extends AbstractTestCase
{
    /**
     * @supportsDebugInfo
     */
    public function testDebugInfo()
    {
        $component = new Host('yolo');
        $this->assertInternalType('array', $component->__debugInfo());
        ob_start();
        var_dump($component);
        $res = ob_get_clean();
        $this->assertContains($component->__toString(), $res);
        $this->assertContains('host', $res);
    }

    public function testSetState()
    {
        $host = new Host('uri.thephpleague.com');
        $this->assertSame('thephpleague.com', $host->getRegisterableDomain());
        $generateHost = eval('return '.var_export($host, true).';');
        $this->assertEquals($host, $generateHost);
    }

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
            'scoped ipv6' => ['[fe80:1234::%251]', true, false, true, '[fe80:1234::%251]'],
            'scoped naked ipv6' => ['fe80:1234::%251', true, false, true, '[fe80:1234::%251]'],
            'normalized' => ['Master.EXAMPLE.cOm', false, false, false, 'master.example.com'],
            'empty string' => ['', false, false, false, ''],
            'null' => [null, false, false, false, ''],
            'dot ending' => ['example.com.', false, false, false, 'example.com.'],
            'partial numeric' => ['23.42c.two', false, false, false, '23.42c.two'],
            'all numeric' => ['98.3.2', false, false, false, '98.3.2'],
            'invalid punycode' => ['xn--fsqu00a.xn--g6w131251d', false, false, false, 'xn--fsqu00a.xn--g6w131251d'],
            'mix IP format with host label' => ['toto.127.0.0.1', false, false, false, 'toto.127.0.0.1'],
        ];
    }

    /**
     * @param string $invalid
     * @dataProvider       invalidHostProvider
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidHost($invalid)
    {
        new Host($invalid);
    }

    public function invalidHostProvider()
    {
        $longlabel = implode('', array_fill(0, 12, 'banana'));

        return [
            'dot in front' => ['.example.com'],
            'hyphen suffix' => ['host.com-'],
            'multiple dot' => ['.......'],
            'one dot' => ['.'],
            'empty label' => ['tot.    .coucou.com'],
            'space in the label' => ['re view'],
            'underscore in label' => ['_bad.host.com'],
            'label too long' => [$longlabel.'.secure.example.com'],
            'too many labels' => [implode('.', array_fill(0, 128, 'a'))],
            'Invalid IPv4 format' => ['[127.0.0.1]'],
            'Invalid IPv6 format' => ['[[::1]]'],
            'Invalid IPv6 format 2' => ['[::1'],
            'space character in starting label' => ['example. com'],
            'invalid character in host label' => ["examp\0le.com"],
            'invalid IP with scope' => ['[127.2.0.1%253]'],
            'invalid scope IPv6' => ['ab23::1234%251'],
            'invalid scope ID' => ['fe80::1234%25?@'],
            'invalid scope ID with utf8 character' => ['fe80::1234%25€'],
            'bool' => [true],
            'Std Class' => [(object) 'foo'],
            'float' => [1.2],
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
     * @param $host
     * @param $expected
     * @dataProvider isIdnProvider
     */
    public function testIsIdn($host, $expected)
    {
        $this->assertSame($expected, (new Host($host))->isIdn());
    }

    public function isIdnProvider()
    {
        return [
            ['127.0.0.1', false],
            ['example.com', false],
            ['مثال.آزمایشی', true],
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
        $host = new Host($unicode);
        $this->assertSame($ascii, (string) $host->toAscii());
        $this->assertSame($unicode, (string) $host->toUnicode());
        $this->assertSame($ascii, (string) $host->toUnicode()->toAscii());
        $this->assertSame($unicode, (string) $host->toAscii()->toUnicode());
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
            ['[::1]', '[::1]'],
            ['127.0.0.1', '127.0.0.1'],
            ['例子.xn--1', 'xn--fsqu00a.xn--1'],
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
            'string' => ['secure.example.com', 3, ['com', 'example', 'secure']],
            'numeric' => ['92.56.8', 3, ['8', '56', '92']],
            'null' => [null, 0, []],
            'empty string' => ['', 1, ['']],
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
            'array' => [['com', 'example', 'www'], Host::IS_RELATIVE, 'www.example.com'],
            'iterator' => [new ArrayIterator(['com', 'example', 'www']), Host::IS_RELATIVE, 'www.example.com'],
            'host object' => [new Host('::1'), Host::IS_RELATIVE, '[::1]'],
            'ip 1' => [[127, 0, 0, 1], Host::IS_RELATIVE, '1.0.0.127'],
            'ip 2' => [['127.0', '0.1'], Host::IS_RELATIVE, '0.1.127.0'],
            'ip 3' => [['127.0.0.1'], Host::IS_RELATIVE, '127.0.0.1'],
            'FQDN' => [['com', 'example', 'www'], Host::IS_ABSOLUTE, 'www.example.com.'],
            'empty' => [[''], Host::IS_RELATIVE, ''],
            'null' => [[], Host::IS_RELATIVE, ''],
            'empty' => [[''], Host::IS_ABSOLUTE, ''],
            'null' => [[], Host::IS_ABSOLUTE, ''],
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
            'object' => [new \stdClass(), Host::IS_RELATIVE],
            'ipv6 FQDN' => [['::1'], Host::IS_ABSOLUTE],
            'unknown flag' => [['all', 'is', 'good'], 23],
        ];
    }

    public function testGetLabel()
    {
        $host = new Host('master.example.com');
        $this->assertSame('com', $host->getLabel(0));
        $this->assertNull($host->getLabel(23));
        $this->assertSame('toto', $host->getLabel(23, 'toto'));
    }

    public function testOffsets()
    {
        $host = new Host('master.example.com');
        $this->assertSame([0, 1, 2], $host->keys());
        $this->assertSame([2], $host->keys('master'));
    }

    /**
     * @dataProvider sameValueAsProvider
     *
     * @param string $host1
     * @param string $host2
     * @param bool   $bool
     */
    public function testSameValueAs($host1, $host2, $bool)
    {
        $this->assertSame($bool, (new Host($host1))->sameValueAs(new Host($host2)));
    }

    public function sameValueAsProvider()
    {
        return [
            'string normalized' => ['master.example.com', 'MaStEr.ExAMple.CoM', true],
            'ip' => ['::1', '::1', true],
            'different string' => ['toto.com', 'barbaz.be', false],
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
            'remove unknown label' => ['secure.example.com', [34], 'secure.example.com'],
            'remove one string label' => ['secure.example.com', [0], 'secure.example'],
            'remove IP based label' => ['127.0.0.1', [0], ''],
            'remove silent excessive label index' => ['127.0.0.1', [0, 1] , ''],
        ];
    }

    /**
     * @param $host
     * @param $expected
     * @dataProvider withoutZoneIdentifierProvider
     */
    public function testWithoutZoneIdentifier($host, $expected)
    {
        $this->assertSame($expected, (new Host($host))->withoutZoneIdentifier()->__toString());
    }

    public function withoutZoneIdentifierProvider()
    {
        return [
            'hostname host' => ['example.com', 'example.com'],
            'ipv4 host' => ['127.0.0.1', '127.0.0.1'],
            'ipv6 host' => ['[::1]', '[::1]'],
            'ipv6 scoped (1)' => ['fe80::%251', '[fe80::]'],
            'ipv6 scoped (2)' => ['fe80::%1', '[fe80::]'],
        ];
    }

    /**
     * @param $host
     * @param $expected
     * @dataProvider hasZoneIdentifierProvider
     */
    public function testHasZoneIdentifier($host, $expected)
    {
        $this->assertSame($expected, (new Host($host))->hasZoneIdentifier());
    }

    public function hasZoneIdentifierProvider()
    {
        return [
            ['127.0.0.1', false],
            ['www.example.com', false],
            ['[::1]', false],
            ['fe80::%251', true],
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
        $host = new Host($raw);
        $newHost = $host->prepend($prepend);
        $this->assertSame($expected, $newHost->__toString());
    }

    public function validPrepend()
    {
        return [
            'prepend host object' => ['secure.example.com', new Host('master'), 'master.secure.example.com'],
            'prepend string' => ['secure.example.com', 'master', 'master.secure.example.com'],
            'prepend FQDN host object' => ['secure.example.com', new Host('master.'), 'master.secure.example.com'],
            'prepend FQDN host string' => ['secure.example.com', 'master.', 'master.secure.example.com'],
            'prepend to FQDN host a host object' => ['secure.example.com.', new Host('master'), 'master.secure.example.com.'],
            'prepend to FQDN host a host string' => ['secure.example.com.', 'master', 'master.secure.example.com.'],
            'prepend IPv4' => ['secure.example.com', '127.0.0.1', '127.0.0.1.secure.example.com'],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testPrependIpFailed()
    {
        (new Host('::1'))->prepend(new Host('foo'));
    }

    /**
     * @param $raw
     * @param $append
     * @param $expected
     * @dataProvider validAppend
     */
    public function testAppend($raw, $append, $expected)
    {
        $host = new Host($raw);
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
            ['127.0.0.1', 'toto', '127.0.0.1.toto'],
        ];
    }

    /**
     * @expectedException LogicException
     */
    public function testAppendIpFailed()
    {
        (new Host('::1'))->append(new Host('foo'));
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
        $this->assertSame($expected, (new Host($raw))->replace($offset, $input)->__toString());
    }

    public function replaceValid()
    {
        return [
            ['master.example.com', new Host('shop'), 2, 'shop.example.com'],
            ['master.example.com', 'shop', 2, 'shop.example.com'],
            ['master.example.com', 'master', 2, 'master.example.com'],
            ['', new Host('::1'), 0, '[::1]'],
            ['', '::1', 0, '[::1]'],
            ['toto', new Host('::1'), 23, 'toto'],
            ['toto', '::1', 23, 'toto'],
            ['127.0.0.1', 'secure.example.com', 2, '127.0.0.1'],
            ['secure.example.com', '127.0.0.1', 0, 'secure.example.127.0.0.1'],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testReplaceIpMustFailed()
    {
        (new Host('secure.example.com'))->replace(2, new Host('::1'));
    }

    /**
     * @dataProvider parseDataProvider
     * @param $host
     * @param $publicSuffix
     * @param $registerableDomain
     * @param $subdomain
     * @param $isValidSuffix
     * @param $ipLiteral
     */
    public function testPublicSuffixListImplementation(
        $host,
        $publicSuffix,
        $registerableDomain,
        $subdomain,
        $isValidSuffix,
        $ipLiteral
    ) {
        $host = new Host($host);
        $this->assertSame($subdomain, $host->getSubdomain());
        $this->assertSame($registerableDomain, $host->getRegisterableDomain());
        $this->assertSame($publicSuffix, $host->getPublicSuffix());
        $this->assertSame($isValidSuffix, $host->isPublicSuffixValid());
        $this->assertSame($ipLiteral, $host->getLiteral());
    }

    public function parseDataProvider()
    {
        return [
            ['www.waxaudio.com.au', 'com.au', 'waxaudio.com.au', 'www', true, 'www.waxaudio.com.au'],
            ['giant.yyyy.', 'yyyy', 'giant.yyyy', '', false, 'giant.yyyy.'],
            ['localhost', '', '', '', false, 'localhost'],
            ['127.0.0.1', '', '', '', false, '127.0.0.1'],
            ['[::1]', '', '', '', false, '::1'],
            ['مثال.إختبار', 'إختبار', 'مثال.إختبار', '', false, 'مثال.إختبار'],
            ['xn--p1ai.ru.', 'ru', 'xn--p1ai.ru', '', true, 'xn--p1ai.ru.'],
        ];
    }
}
