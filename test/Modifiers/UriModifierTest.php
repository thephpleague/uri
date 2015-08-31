<?php

namespace League\Uri\Test\Modifiers;

use League\Uri\Modifiers\Relativize;
use League\Uri\Modifiers\Resolve;
use League\Uri\Schemes\Data as DataUri;
use League\Uri\Schemes\Ftp as FtpUri;
use League\Uri\Schemes\Http as HttpUri;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @group uri
 * @group modifier
 */
class UriModifierTest extends TestCase
{
    /**
     * @dataProvider relativizeProvider
     * @param $base
     * @param $child
     * @param $expected
     */
    public function testRelativize($base, $child, $expected)
    {
        $baseUri  = HttpUri::createFromString($base);
        $childUri = HttpUri::createFromString($child);
        $modifier = (new Relativize(HttpUri::createFromString()))->withUri($baseUri);

        $this->assertSame($expected, (string) $modifier->__invoke($childUri));
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

    public function testRelativizeWithNonHierarchicalUri()
    {
        $httpUri = HttpUri::createFromString();
        $dataUri = DataUri::createFromString('data:text/plain;charset=us-ascii,Bonjour%20le%20monde%21');
        $modifier = new Relativize($httpUri);

        $this->assertSame($dataUri, $modifier->__invoke($dataUri));
    }

    /**
     * @dataProvider mixUriProvider
     * @param $input
     * @param $relative
     */
    public function testRelativizeUriObject($input, $relative)
    {
        $this->assertSame($relative, (new Relativize($input))->__invoke($relative));
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

    /**
     * @dataProvider resolveProvider
     * @param $uri
     * @param $relative
     * @param $expected
     */
    public function testResolve($uri, $relative, $expected)
    {
        $uri      = HttpUri::createFromString($uri);
        $relative = HttpUri::createFromString($relative);
        $modifier = new Resolve($uri);

        $this->assertSame($expected, (string) $modifier->__invoke($relative));
    }

    public function resolveProvider()
    {
        $base_uri = 'http://a/b/c/d;p?q';

        return [
          'base uri' =>                [$base_uri, '',               $base_uri],
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

    public function testResolveUri()
    {
        $http = HttpUri::createFromString('http://example.com/path/to/file');
        $dataUri = DataUri::createFromString('data:text/plain;charset=us-ascii,Bonjour%20le%20monde!');
        $modifier = new Resolve($http);
        $this->assertSame($dataUri, $modifier->__invoke($dataUri));
    }

    /**
     * @expectedException InvalidArgumentException 
     */
    public function resolveLetThrowResolvedUriException()
    {
        $http = HttpUri::createFromString('http://example.com/path/to/file');
        $ftp = FtpUri::createFromString('//a/b/c/d;p');
        $modifier = new Resolve($http);
        $modifier->__invoke($ftp);
    }
}
