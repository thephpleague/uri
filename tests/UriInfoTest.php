<?php

/**
 * League.Uri (https://uri.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LeagueTest\Uri;

use League\Uri\Http;
use League\Uri\Uri;
use League\Uri\UriInfo;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface as Psr7UriInterface;
use TypeError;

/**
 * @group modifier
 * @coversDefaultClass League\Uri\UriInfo
 */
class UriInfoTest extends TestCase
{
    /**
     * @dataProvider uriProvider
     *
     * @param Psr7UriInterface|Uri      $uri
     * @param null|Psr7UriInterface|Uri $base_uri
     * @param bool[]                    $infos
     */
    public function testInfo($uri, $base_uri, array $infos): void
    {
        if (null !== $base_uri) {
            self::assertSame($infos['same_document'], UriInfo::isSameDocument($uri, $base_uri));
        }
        self::assertSame($infos['relative_path'], UriInfo::isRelativePath($uri));
        self::assertSame($infos['absolute_path'], UriInfo::isAbsolutePath($uri));
        self::assertSame($infos['absolute_uri'], UriInfo::isAbsolute($uri));
        self::assertSame($infos['network_path'], UriInfo::isNetworkPath($uri));
    }

    public function uriProvider(): array
    {
        return [
            'absolute uri' => [
                'uri' => Http::createFromString('http://a/p?q#f'),
                'base_uri' => null,
                'infos' => [
                    'absolute_uri' => true,
                    'network_path' => false,
                    'absolute_path' => false,
                    'relative_path' => false,
                    'same_document' => false,
                ],
            ],
            'network relative uri' => [
                'uri' => Http::createFromString('//스타벅스코리아.com/p?q#f'),
                'base_uri' => Http::createFromString('//xn--oy2b35ckwhba574atvuzkc.com/p?q#z'),
                'infos' => [
                    'absolute_uri' => false,
                    'network_path' => true,
                    'absolute_path' => false,
                    'relative_path' => false,
                    'same_document' => true,
                ],
            ],
            'path relative uri with non empty path' => [
                'uri' => Http::createFromString('p?q#f'),
                'base_uri' => null,
                'infos' => [
                    'absolute_uri' => false,
                    'network_path' => false,
                    'absolute_path' => false,
                    'relative_path' => true,
                    'same_document' => false,
                ],
            ],
            'path relative uri with empty' => [
                'uri' => Http::createFromString('?q#f'),
                'base_uri' => null,
                'infos' => [
                    'absolute_uri' => false,
                    'network_path' => false,
                    'absolute_path' => false,
                    'relative_path' => true,
                    'same_document' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider failedUriProvider
     *
     * @param null|mixed $uri
     * @param null|mixed $base_uri
     */
    public function testStatThrowsInvalidArgumentException($uri, $base_uri): void
    {
        self::expectException(TypeError::class);
        UriInfo::isSameDocument($uri, $base_uri);
    }

    public function failedUriProvider(): array
    {
        return [
            'invalid uri' => [
                'uri' => Http::createFromString('http://a/p?q#f'),
                'base_uri' => 'http://example.com',
            ],
            'invalid base uri' => [
                'uri' => 'http://example.com',
                'base_uri' => Http::createFromString('//a/p?q#f'),
            ],
        ];
    }

    /**
     * @dataProvider functionProvider
     */
    public function testIsFunctionsThrowsTypeError(string $function): void
    {
        self::expectException(TypeError::class);

        UriInfo::$function('http://example.com');
    }

    public function functionProvider(): array
    {
        return [
            ['isAbsolute'],
            ['isNetworkPath'],
            ['isAbsolutePath'],
            ['isRelativePath'],
        ];
    }

    /**
     * @dataProvider sameValueAsProvider
     *
     * @param Psr7UriInterface|Uri $uri1
     * @param Psr7UriInterface|Uri $uri2
     */
    public function testSameValueAs($uri1, $uri2, bool $expected): void
    {
        self::assertSame($expected, UriInfo::isSameDocument($uri1, $uri2));
    }

    public function sameValueAsProvider(): array
    {
        return [
            '2 disctincts URIs' => [
                Http::createFromString('http://example.com'),
                Uri::createFromString('ftp://example.com'),
                false,
            ],
            '2 identical URIs' => [
                Http::createFromString('http://example.com'),
                Http::createFromString('http://example.com'),
                true,
            ],
            '2 identical URIs after removing dot segment' => [
                Http::createFromString('http://example.org/~foo/'),
                Http::createFromString('http://example.ORG/bar/./../~foo/'),
                true,
            ],
            '2 distincts relative URIs' => [
                Http::createFromString('~foo/'),
                Http::createFromString('../~foo/'),
                false,
            ],
            '2 identical relative URIs' => [
                Http::createFromString('../%7efoo/'),
                Http::createFromString('../~foo/'),
                true,
            ],
            '2 identical URIs after normalization (1)' => [
                Http::createFromString('HtTp://مثال.إختبار:80/%7efoo/%7efoo/'),
                Http::createFromString('http://xn--mgbh0fb.xn--kgbechtv/%7Efoo/~foo/'),
                true,
            ],
            '2 identical URIs after normalization (2)' => [
                Http::createFromString('http://www.example.com'),
                Http::createFromString('http://www.example.com/'),
                true,
            ],
            '2 identical URIs after normalization (3)' => [
                Http::createFromString('http://www.example.com'),
                Http::createFromString('http://www.example.com:/'),
                true,
            ],
            '2 identical URIs after normalization (4)' => [
                Http::createFromString('http://www.example.com'),
                Http::createFromString('http://www.example.com:80/'),
                true,
            ],
        ];
    }
}
