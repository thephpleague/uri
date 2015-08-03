<?php

namespace League\Uri\test\Schemes\Uri;

use League\Uri\Components;
use League\Uri\Schemes\Data as DataUri;
use League\Uri\Schemes\Ftp as FtpUri;
use League\Uri\Schemes\Http as HttpUri;
use PHPUnit_Framework_TestCase;

/**
 * @group uri
 */
class HierarchicalUriTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Url
     */
    private $uri;

    public function setUp()
    {
        $this->uri = HttpUri::createFromString(
            'http://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3'
        );
    }

    public function tearDown()
    {
        $this->uri = null;
    }

    public function testGetterAccess()
    {
        $this->assertSame($this->uri->getScheme(), $this->uri->scheme->__toString());
        $this->assertSame($this->uri->getUserInfo(), $this->uri->userInfo->__toString());
        $this->assertSame($this->uri->getHost(), $this->uri->host->__toString());
        $this->assertSame($this->uri->getPort(), $this->uri->port->toInt());
        $this->assertSame($this->uri->getPath(), $this->uri->path->__toString());
        $this->assertSame($this->uri->getQuery(), $this->uri->query->__toString());
        $this->assertSame($this->uri->getFragment(), $this->uri->fragment->__toString());
    }

    public function testKeepSameInstanceIfPropertyDoesNotChange()
    {
        $this->assertSame($this->uri, $this->uri->withScheme('http'));
        $this->assertSame($this->uri, $this->uri->withUserInfo('login', 'pass'));
        $this->assertSame($this->uri, $this->uri->withHost('secure.example.com'));
        $this->assertSame($this->uri, $this->uri->withPort(443));
        $this->assertSame($this->uri, $this->uri->withPath('/test/query.php'));
        $this->assertSame($this->uri, $this->uri->withQuery('kingkong=toto'));
        $this->assertSame($this->uri, $this->uri->withFragment('doc3'));
    }

    public function testCreateANewInstanceWhenPropertyChanges()
    {
        $this->assertNotEquals($this->uri, $this->uri->withScheme('https'));
        $this->assertNotEquals($this->uri, $this->uri->withUserInfo('login', null));
        $this->assertNotEquals($this->uri, $this->uri->withHost('shop.example.com'));
        $this->assertNotEquals($this->uri, $this->uri->withPort(81));
        $this->assertNotEquals($this->uri, $this->uri->withPath('/test/file.php'));
        $this->assertNotEquals($this->uri, $this->uri->withQuery('kingkong=tata'));
        $this->assertNotEquals($this->uri, $this->uri->withFragment('doc2'));
    }

    public function testGetAuthority()
    {
        $this->assertSame('login:pass@secure.example.com:443', $this->uri->getAuthority());
    }

    public function testGetUserInfo()
    {
        $this->assertSame('login:pass', $this->uri->getUserInfo());
    }

    public function testAutomaticUrlNormalization()
    {
        $raw = 'HtTpS://MaStEr.eXaMpLe.CoM:443/%7ejohndoe/%a1/in+dex.php?foo.bar=value#fragment';
        $normalized = 'https://master.example.com/~johndoe/%A1/in+dex.php?foo.bar=value#fragment';
        $this->assertSame($normalized, (string) HttpUri::createFromString($raw));
    }

    /**
     * @param $uri
     * @param $port
     * @dataProvider portProvider
     */
    public function testPort($uri, $port)
    {
        $this->assertSame($port, HttpUri::createFromString($uri)->getPort());
    }

    public function portProvider()
    {
        return [
            ['http://www.example.com:443/', 443],
            ['http://www.example.com:80/', null],
            ['http://www.example.com', null],
            ['//www.example.com:80/', 80],
        ];
    }

    /**
     * @param $uri
     * @param $expected
     * @dataProvider toArrayProvider
     */
    public function testToArray($uri, $expected)
    {
        $this->assertSame($expected, HttpUri::createFromString($uri)->toArray());
    }

    public function toArrayProvider()
    {
        return [
            'simple' => [
                'http://toto.com:443/toto.php',
                [
                    'scheme' => 'http',
                    'user' => null,
                    'pass' => null,
                    'host' => 'toto.com',
                    'port' => 443,
                    'path' => '/toto.php',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'ipv6 host' => [
                'https://[::1]:443/toto.php',
                [
                    'scheme' => 'https',
                    'user' => null,
                    'pass' => null,
                    'host' => '[::1]',
                    'port' => null,
                    'path' => '/toto.php',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'missing host' => [
                '/toto.php',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => '/toto.php',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'relative path' => [
                'toto.php#fragment',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'toto.php',
                    'query' => null,
                    'fragment' => 'fragment',
                ],
            ],
        ];
    }

    /**
     * @param $uri
     * @param $expected
     * @dataProvider isEmptyProvider
     */
    public function testIsEmpty($uri, $expected)
    {
        $this->assertSame($expected, $uri->isEmpty());
    }

    public function isEmptyProvider()
    {
        return [
            'normal URI' => [HttpUri::createFromString('http://a/b/c'), false],
            'incomplete authority' => [new HttpUri(
                new Components\Scheme(),
                new Components\UserInfo('foo', 'bar'),
                new Components\Host(),
                new Components\Port(80),
                new Components\HierarchicalPath(),
                new Components\Query(),
                new Components\Fragment()
            ), true],
            'empty URI components' => [new HttpUri(
                new Components\Scheme(),
                new Components\UserInfo(),
                new Components\Host(),
                new Components\Port(),
                new Components\HierarchicalPath(),
                new Components\Query(),
                new Components\Fragment()
            ), true],
        ];
    }

    /**
     * @dataProvider sameValueAsPsr7InterfaceProvider
     */
    public function testSameValueAs($league, $psr7, $expected)
    {
        $mock = $this->getMock('Psr\Http\Message\UriInterface');
        $mock->method('__toString')->willReturn($psr7);

        $uri = HttpUri::createFromString($league);

        $this->assertSame($expected, $uri->sameValueAs($mock));
    }

    public function sameValueAsPsr7InterfaceProvider()
    {
        return [
            ['http://example.com', 'yolo://example.com', false],
            ['http://example.com', 'http://example.com', true],
            ['//example.com', '//ExamPle.Com', true],
            ['http://مثال.إختبار', 'http://xn--mgbh0fb.xn--kgbechtv', true],
            ['http://example.com', 'http:///example.com', false],
            ['http://example.com', 'http:example.com', false],
            ['http://example.com', 'http:/example.com', false],
            ['http://example.org/~foo/', 'HTTP://example.ORG/~foo/', true],
            ['http://example.org/~foo/', 'http://example.org:80/~foo/', true],
            ['http://example.org/~foo/', 'http://example.org/%7Efoo/', true],
            ['http://example.org/~foo/', 'http://example.org/%7efoo/', true],
            ['http://example.org/~foo/', 'http://example.ORG/bar/./../~foo/', true],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSameValueAsFailedWithUnknownType()
    {
        HttpUri::createFromString('http://example.com')->sameValueAs('http://example.com');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithSchemeFailedWithUnsupportedScheme()
    {
        HttpUri::createFromString('http://example.com')->withScheme('telnet');
    }

    /**
     * @param $uri
     * @param $expected
     * @dataProvider pathFormattingProvider
     */
    public function testPathFormatting($uri, $expected)
    {
        $this->assertSame($expected, $uri->__toString());
    }

    public function pathFormattingProvider()
    {
        return [
            [new HttpUri(
                new Components\Scheme('http'),
                new Components\UserInfo(),
                new Components\Host('ExAmPLe.cOm'),
                new Components\Port(),
                new Components\HierarchicalPath('path/to/the/sky'),
                new Components\Query(),
                new Components\Fragment()
            ), 'http://example.com/path/to/the/sky'],
            [new HttpUri(
                new Components\Scheme(''),
                new Components\UserInfo(),
                new Components\Host(),
                new Components\Port(),
                new Components\HierarchicalPath('///path/to/the/sky'),
                new Components\Query(),
                new Components\Fragment()
            ), '/path/to/the/sky'],
        ];
    }

    /**
     * @dataProvider hasStandardPortProvider
     */
    public function testHasStandardPort($uri, $expected)
    {
        $this->assertSame($expected, HttpUri::createFromString($uri)->hasStandardPort());
    }

    public function hasStandardPortProvider()
    {
        return [
            ['http://example.com:81/', false],
            ['http://example.com:80/', true],
            ['http://example.com/', true],
        ];
    }

    /**
     * @dataProvider resolveProvider
     */
    public function testResolve($uri, $relative, $expected)
    {
        $uri      = HttpUri::createFromString($uri);
        $relative = HttpUri::createFromString($relative);

        $this->assertSame($expected, (string) $uri->resolve($relative));
    }

    public function resolveProvider()
    {
        $base_uri = 'http://a/b/c/d;p?q';

        return [
          'base uri' =>                 [$base_uri, '',               $base_uri],
          'scheme' =>                  [$base_uri, 'http://d/e/f',   'http://d/e/f'],
          'path 1' =>                  [$base_uri, 'g',              'http://a/b/c/g'],
          'path 2' =>                  [$base_uri, './g',            'http://a/b/c/g'],
          'path 3' =>                  [$base_uri, 'g/',             'http://a/b/c/g/'],
          'path 4' =>                  [$base_uri, '/g',             'http://a/g'],
          'authority' =>               [$base_uri, '//g',            'http://g'],
          'query' =>                   [$base_uri, '?y',             'http://a/b/c/d;p?y'],
          'path + query' =>            [$base_uri, 'g?y',            'http://a/b/c/g?y'],
          'fragment' =>                [$base_uri, '#s',             'http://a/b/c/d;p?q#s'],
          'path + fragment' =>         [$base_uri, 'g#s',            'http://a/b/c/g#s'],
          'path + query + fragment' => [$base_uri, 'g?y#s',          'http://a/b/c/g?y#s'],
          'single dot 1' =>            [$base_uri, '.',              'http://a/b/c/'],
          'single dot 2' =>            [$base_uri, './',             'http://a/b/c/'],
          'single dot 3' =>            [$base_uri, './g/.',          'http://a/b/c/g/'],
          'single dot 4' =>            [$base_uri, 'g/./h',          'http://a/b/c/g/h'],
          'double dot 1' =>            [$base_uri, '..',             'http://a/b/'],
          'double dot 2' =>            [$base_uri, '../',            'http://a/b/'],
          'double dot 3' =>            [$base_uri, '../g',           'http://a/b/g'],
          'double dot 4' =>            [$base_uri, '../..',          'http://a/'],
          'double dot 5' =>            [$base_uri, '../../',         'http://a/'],
          'double dot 6' =>            [$base_uri, '../../g',        'http://a/g'],
          'double dot 7' =>            [$base_uri, '../../../g',     'http://a/g'],
          'double dot 8' =>            [$base_uri, '../../../../g',  'http://a/g'],
          'double dot 9' =>            [$base_uri, 'g/../h' ,        'http://a/b/c/h'],
          'mulitple slashes' =>        [$base_uri, 'foo////g',       'http://a/b/c/foo////g'],
          'complex path 1' =>          [$base_uri, ';x',             'http://a/b/c/;x'],
          'complex path 2' =>          [$base_uri, 'g;x',            'http://a/b/c/g;x'],
          'complex path 3' =>          [$base_uri, 'g;x?y#s',        'http://a/b/c/g;x?y#s'],
          'complex path 4' =>          [$base_uri, 'g;x=1/./y',      'http://a/b/c/g;x=1/y'],
          'complex path 5' =>          [$base_uri, 'g;x=1/../y',     'http://a/b/c/y'],
          'origin uri without path' => ['http://h:b@a', 'b/../y',    'http://h:b@a/y'],
          '2 relative paths 1'      => ['a/b',          '../..',     '/'],
          '2 relative paths 2'      => ['a/b',          './.',       'a/'],
          '2 relative paths 3'      => ['a/b',          '../c',      'c'],
          '2 relative paths 4'      => ['a/b',          'c/..',      'a/'],
          '2 relative paths 5'      => ['a/b',          'c/.',       'a/c/'],
        ];
    }

    /**
     * @dataProvider relativizeProvider
     */
    public function testRelativize($base, $child, $expected)
    {
        $baseUri  = HttpUri::createFromString($base);
        $childUri = HttpUri::createFromString($child);

        $this->assertSame($expected, (string) $baseUri->relativize($childUri));
    }

    /**
     * @dataProvider resolveUriProvider
     */
    public function testResolveUri($uri1, $uri2)
    {
        $this->assertSame($uri2, $uri1->resolve($uri2));
    }

    public function resolveUriProvider()
    {
        return [
            'two hierarchical uri' => [
                FtpUri::createFromString('ftp://example.com/path/to/file'),
                HttpUri::createFromString('//a/b/c/d;p?q'),
            ],
            'hierarchical uri resolve opaque uri' => [
                FtpUri::createFromString('ftp://example.com/path/to/file'),
                DataUri::createFromString('data:text/plain;charset=us-ascii,Bonjour%20le%20monde%21'),
            ],
            'opaque uri resolve hierarchical uri' => [
                DataUri::createFromString('data:text/plain;charset=us-ascii,Bonjour%20le%20monde%21'),
                FtpUri::createFromString('/toto/le/heros'),
            ],
        ];
    }

    /**
     * @dataProvider mixUriProvider
     */
    public function testRelativizeUriObject($input, $relative)
    {
        $this->assertSame($relative, $input->relativize($relative));
    }

    public function mixUriProvider()
    {
        return [
            [
                FtpUri::createFromString('ftp://example.com/path/to/file'),
                HttpUri::createFromString('//a/b/c/d;p?q'),
            ],
            [
                FtpUri::createFromString('//example.com/path/to/file'),
                HttpUri::createFromString('./g'),
            ],
        ];
    }

    public function relativizeProvider()
    {
        return [
            ['http://www.example.com/foo/bar', 'http://toto.com', 'http://toto.com'],
            ['http://www.example.com/foo/bar', 'http://www.example.com:81/foo', 'http://www.example.com:81/foo'],
            ['http://www.example.com/toto/le/heros', 'http://www.example.com/bar', '../bar'],
            ['http://www.example.com/toto/le/heros/', 'http://www.example.com/bar', '../bar'],
            ['http://www.example.com/toto/le/../heros/', 'http://www.example.com/../bar', 'bar'],
            ['http://www.example.com/toto/le/heros/', 'http://www.example.com/bar?query=value', '../bar?query=value'],
        ];
    }

    /**
     * @dataProvider invalidURI
     * @expectedException InvalidArgumentException
     */
    public function testCreateFromInvalidUrlKO($input)
    {
        HttpUri::createFromString($input);
    }

    public function invalidURI()
    {
        return [
            ['http://user@:80'],
            ['//user@:80'],
        ];
    }

    public function testLazyLoadingUriParser()
    {
        $uri = DataUri::createFromString('data:,');
        $parser = (new \ReflectionClass($uri))->getProperty('uriParser');
        $parser->setAccessible(true);
        $parser = $parser->setValue(null);
        $newUri = $uri->withParameters('charset=utf-8');
        $this->assertInternalType('array', $newUri->toArray());
        $altParser = (new \ReflectionClass($newUri))->getProperty('uriParser');
        $altParser->setAccessible(true);
        $this->assertInstanceOf('\League\Uri\Parser', $altParser->getValue());
    }
}
