<?php

namespace League\Uri\Test\Modifiers;

use InvalidArgumentException;
use League\Uri\Modifiers\Normalize;
use League\Uri\Modifiers\Relativize;
use League\Uri\Modifiers\Resolve;
use League\Uri\Schemes\Data as DataUri;
use League\Uri\Schemes\Ftp as FtpUri;
use League\Uri\Schemes\Http as HttpUri;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @group uri
 * @group modifier
 * @group uri-modifier
 */
class UriModifierTest extends TestCase
{
    const BASE_URI = 'http://a/b/c/d;p?q';

    /**
     * @dataProvider resolveProvider
     * @param $uri
     * @param $relative
     * @param $expected
     */
    public function testResolve($uri, $relative, $expected)
    {
        $uri = HttpUri::createFromString($uri);
        $relative = HttpUri::createFromString($relative);
        $modifier = new Resolve($uri);

        $this->assertSame($expected, (string) $modifier->__invoke($relative));
    }

    public function resolveProvider()
    {
        return [
            'base uri' => [self::BASE_URI, '',              self::BASE_URI],
            'scheme' => [self::BASE_URI, 'http://d/e/f',  'http://d/e/f'],
            'path 1' => [self::BASE_URI, 'g',             'http://a/b/c/g'],
            'path 2' => [self::BASE_URI, './g',           'http://a/b/c/g'],
            'path 3' => [self::BASE_URI, 'g/',            'http://a/b/c/g/'],
            'path 4' => [self::BASE_URI, '/g',            'http://a/g'],
            'authority' => [self::BASE_URI, '//g',           'http://g'],
            'query' => [self::BASE_URI, '?y',            'http://a/b/c/d;p?y'],
            'path + query' => [self::BASE_URI, 'g?y',           'http://a/b/c/g?y'],
            'fragment' => [self::BASE_URI, '#s',            'http://a/b/c/d;p?q#s'],
            'path + fragment' => [self::BASE_URI, 'g#s',           'http://a/b/c/g#s'],
            'path + query + fragment' => [self::BASE_URI, 'g?y#s',         'http://a/b/c/g?y#s'],
            'single dot 1' => [self::BASE_URI, '.',             'http://a/b/c/'],
            'single dot 2' => [self::BASE_URI, './',            'http://a/b/c/'],
            'single dot 3' => [self::BASE_URI, './g/.',         'http://a/b/c/g/'],
            'single dot 4' => [self::BASE_URI, 'g/./h',         'http://a/b/c/g/h'],
            'double dot 1' => [self::BASE_URI, '..',            'http://a/b/'],
            'double dot 2' => [self::BASE_URI, '../',           'http://a/b/'],
            'double dot 3' => [self::BASE_URI, '../g',          'http://a/b/g'],
            'double dot 4' => [self::BASE_URI, '../..',         'http://a/'],
            'double dot 5' => [self::BASE_URI, '../../',        'http://a/'],
            'double dot 6' => [self::BASE_URI, '../../g',       'http://a/g'],
            'double dot 7' => [self::BASE_URI, '../../../g',    'http://a/g'],
            'double dot 8' => [self::BASE_URI, '../../../../g', 'http://a/g'],
            'double dot 9' => [self::BASE_URI, 'g/../h' ,       'http://a/b/c/h'],
            'mulitple slashes' => [self::BASE_URI, 'foo////g',      'http://a/b/c/foo////g'],
            'complex path 1' => [self::BASE_URI, ';x',            'http://a/b/c/;x'],
            'complex path 2' => [self::BASE_URI, 'g;x',           'http://a/b/c/g;x'],
            'complex path 3' => [self::BASE_URI, 'g;x?y#s',       'http://a/b/c/g;x?y#s'],
            'complex path 4' => [self::BASE_URI, 'g;x=1/./y',     'http://a/b/c/g;x=1/y'],
            'complex path 5' => [self::BASE_URI, 'g;x=1/../y',    'http://a/b/c/y'],
            'dot segments presence 1' => [self::BASE_URI, '/./g',          'http://a/g'],
            'dot segments presence 2' => [self::BASE_URI, '/../g',         'http://a/g'],
            'dot segments presence 3' => [self::BASE_URI, 'g.',            'http://a/b/c/g.'],
            'dot segments presence 4' => [self::BASE_URI, '.g',            'http://a/b/c/.g'],
            'dot segments presence 5' => [self::BASE_URI, 'g..',           'http://a/b/c/g..'],
            'dot segments presence 6' => [self::BASE_URI, '..g',           'http://a/b/c/..g'],
            'origin uri without path' => ['http://h:b@a', 'b/../y',        'http://h:b@a/y'],
            '2 relative paths 1' => ['a/b',          '../..',         '/'],
            '2 relative paths 2' => ['a/b',          './.',           'a/'],
            '2 relative paths 3' => ['a/b',          '../c',          'c'],
            '2 relative paths 4' => ['a/b',          'c/..',          'a/'],
            '2 relative paths 5' => ['a/b',          'c/.',           'a/c/'],
        ];
    }

    public function testResolveUri()
    {
        $http = HttpUri::createFromString('http://example.com/path/to/file');
        $dataUri = DataUri::createFromString('data:text/plain;charset=us-ascii,Bonjour%20le%20monde!');
        $modifier = (new Resolve($http))->withUri($http);
        $this->assertSame($dataUri, $modifier->__invoke($dataUri));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function resolveLetThrowResolvedUriException()
    {
        $http = HttpUri::createFromString('http://example.com/path/to/file');
        $ftp = FtpUri::createFromString('ftp//a/b/c/d;p');
        $modifier = new Resolve($http);
        $modifier->__invoke($ftp);
    }

    /**
     * @dataProvider relativizeProvider
     */
    public function testRelativize($uri, $resolved, $expected)
    {
        $uri = HttpUri::createFromString($uri);
        $resolved = HttpUri::createFromString($resolved);
        $modifier = new Relativize($uri);

        $this->assertSame($expected, (string) $modifier->__invoke($resolved));
    }

    public function relativizeProvider()
    {
        return [
            'different scheme' => [self::BASE_URI,       'https://a/b/c/d;p?q',   'https://a/b/c/d;p?q'],
            'different authority' => [self::BASE_URI,       'https://g/b/c/d;p?q',   'https://g/b/c/d;p?q'],
            'empty uri' => [self::BASE_URI,       '',                      ''],
            'same uri' => [self::BASE_URI,       self::BASE_URI,          ''],
            'same path' => [self::BASE_URI,       'http://a/b/c/d;p',      'd;p'],
            'parent path 1' => [self::BASE_URI,       'http://a/b/c/',         './'],
            'parent path 2' => [self::BASE_URI,       'http://a/b/',           '../'],
            'parent path 3' => [self::BASE_URI,       'http://a/',             '../../'],
            'parent path 4' => [self::BASE_URI,       'http://a',              '../../'],
            'sibling path 1' => [self::BASE_URI,       'http://a/b/c/g',        'g'],
            'sibling path 2' => [self::BASE_URI,       'http://a/b/c/g/h',      'g/h'],
            'sibling path 3' => [self::BASE_URI,       'http://a/b/g',          '../g'],
            'sibling path 4' => [self::BASE_URI,       'http://a/g',            '../../g'],
            'query' => [self::BASE_URI,       'http://a/b/c/d;p?y',    '?y'],
            'fragment' => [self::BASE_URI,       'http://a/b/c/d;p?q#s',  '#s'],
            'path + query' => [self::BASE_URI,       'http://a/b/c/g?y',      'g?y'],
            'path + fragment' => [self::BASE_URI,       'http://a/b/c/g#s',      'g#s'],
            'path + query + fragment' => [self::BASE_URI,       'http://a/b/c/g?y#s',    'g?y#s'],
            'empty segments' => [self::BASE_URI,       'http://a/b/c/foo////g', 'foo////g'],
            'empty segments 1' => [self::BASE_URI,       'http://a/b////c/foo/g', '..////c/foo/g'],
            'relative single dot 1' => [self::BASE_URI,       '.',                     '.'],
            'relative single dot 2' => [self::BASE_URI,       './',                    './'],
            'relative double dot 1' => [self::BASE_URI,       '..',                    '..'],
            'relative double dot 2' => [self::BASE_URI,       '../',                   '../'],
            'path with colon 1' => ['http://a/',          'http://a/d:p',          './d:p'],
            'path with colon 2' => [self::BASE_URI,       'http://a/b/c/g/d:p',    'g/d:p'],
            'scheme + auth 1' => ['http://a',           'http://a?q#s',          '?q#s'],
            'scheme + auth 2' => ['http://a/',          'http://a?q#s',          '/?q#s'],
            '2 relative paths 1' => ['a/b',                '../..',                 '../..'],
            '2 relative paths 2' => ['a/b',                './.',                   './.'],
            '2 relative paths 3' => ['a/b',                '../c',                  '../c'],
            '2 relative paths 4' => ['a/b',                'c/..',                  'c/..'],
            '2 relative paths 5' => ['a/b',                'c/.',                   'c/.'],
            'baseUri with query' => ['/a/b/?q',            '/a/b/#h',               './#h'],
            'targetUri with fragment' => ['/',                  '/#h',                   '#h'],
            'same document' => ['/',                  '/',                     ''],
            'same URI normalized' => ['http://a',           'http://a/',             ''],
        ];
    }

    /**
     * @dataProvider relativizeAndResolveProvider
     */
    public function testRelativizeAndResolve(
        $baseUri,
        $uri,
        $expectedRelativize,
        $expectedResolved
    ) {
        $baseUri = HttpUri::createFromString($baseUri);
        $resolver = new Resolve($baseUri);
        $relativizer = new Relativize($baseUri);
        $uri = HttpUri::createFromString($uri);

        $relativeUri = $relativizer($uri);
        $resolvedUri = $resolver($relativeUri);

        $this->assertSame($expectedRelativize, (string) $relativeUri);
        $this->assertSame($expectedResolved, (string) $resolvedUri);
    }

    public function relativizeAndResolveProvider()
    {
        return [
            'empty path' => [self::BASE_URI, 'http://a/', '../../',   'http://a/'],
            'absolute empty path' => [self::BASE_URI, 'http://a',  '../../',   'http://a/'],
            'relative single dot 1' => [self::BASE_URI, '.',         '.',        'http://a/b/c/'],
            'relative single dot 2' => [self::BASE_URI, './',        './',       'http://a/b/c/'],
            'relative double dot 1' => [self::BASE_URI, '..',        '..',       'http://a/b/'],
            'relative double dot 2' => [self::BASE_URI, '../',       '../',      'http://a/b/'],
            '2 relative paths 1' => ['a/b',          '../..',     '../..',    '/'],
            '2 relative paths 2' => ['a/b',          './.',       './.',      'a/'],
            '2 relative paths 3' => ['a/b',          '../c',      '../c',     'c'],
            '2 relative paths 4' => ['a/b',          'c/..',      'c/..',     'a/'],
            '2 relative paths 5' => ['a/b',          'c/.',       'c/.',      'a/c/'],
            'path with colon' => ['http://a/',    'http://a/d:p', './d:p', 'http://a/d:p'],
        ];
    }

    /**
     * @dataProvider sameValueAsProvider
     *
     * @param HttpUri $league
     * @param FtpUri  $psr7
     * @param bool    $expected
     */
    public function testSameValueAs($league, $psr7, $expected)
    {
        $modifier = new Normalize();
        $this->assertSame(
            $expected,
            $modifier($league)->__toString() === $modifier($psr7)->__toString()
        );
    }

    public function testNormalizeDoesNotAlterPathEncoding()
    {
        $rawUrl = 'HtTp://vonNN.com/ipsam-nulla-adipisci-laboriosam-dignissimos-accusamus-eum-voluptatem';
        $uriNormalizer = new Normalize();
        $uri = (string) $uriNormalizer(HttpUri::createFromString($rawUrl));
        $this->assertSame('http://vonnn.com/ipsam-nulla-adipisci-laboriosam-dignissimos-accusamus-eum-voluptatem', $uri);
    }

    public function sameValueAsProvider()
    {
        return [
            [HttpUri::createFromString('http://example.com'), FtpUri::createFromString('ftp://example.com'), false],
            [HttpUri::createFromString('http://example.com'), HttpUri::createFromString('http://example.com'), true],
            [HttpUri::createFromString('//example.com'), HttpUri::createFromString('//ExamPle.Com'), true],
            [HttpUri::createFromString('http://مثال.إختبار'), HttpUri::createFromString('http://xn--mgbh0fb.xn--kgbechtv'), true],
            [HttpUri::createFromString('http://example.com'), DataUri::createFromPath(dirname(__DIR__).'/data/red-nose.gif'), false],
            [HttpUri::createFromString('http://example.org/~foo/'), HttpUri::createFromString('HTTP://example.ORG/~foo/'), true],
            [HttpUri::createFromString('http://example.org/~foo/'), HttpUri::createFromString('http://example.org:80/~foo/'), true],
            [HttpUri::createFromString('http://example.org/%7efoo/'), HttpUri::createFromString('http://example.org/%7Efoo/'), true],
            [HttpUri::createFromString('http://example.org/~foo/'), HttpUri::createFromString('http://example.ORG/bar/./../~foo/'), true],
            [HttpUri::createFromString('/%7efoo/'), HttpUri::createFromString('/~foo/'), true],
            [HttpUri::createFromString('../%7efoo/'), HttpUri::createFromString('../~foo/'), true],
        ];
    }
}
