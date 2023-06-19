<?php

/**
 * League.Uri (https://uri.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Uri;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface as Psr7UriInterface;

/**
 * @group modifier
 * @coversDefaultClass \League\Uri\UriInfo
 */
final class UriInfoTest extends TestCase
{
    /**
     * @dataProvider uriProvider
     *
     * @param array<bool> $infos
     */
    public function testInfo(
        Psr7UriInterface|Uri $uri,
        Psr7UriInterface|Uri|null $base_uri,
        array $infos
    ): void {
        if (null !== $base_uri) {
            self::assertSame($infos['same_document'], UriInfo::isSameDocument($uri, $base_uri));
        }
        self::assertSame($infos['relative_path'], UriInfo::isRelativePath($uri));
        self::assertSame($infos['absolute_path'], UriInfo::isAbsolutePath($uri));
        self::assertSame($infos['absolute_uri'], UriInfo::isAbsolute($uri));
        self::assertSame($infos['network_path'], UriInfo::isNetworkPath($uri));
    }

    public static function uriProvider(): array
    {
        return [
            'absolute uri' => [
                'uri' => Http::new('http://a/p?q#f'),
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
                'uri' => Http::new('//스타벅스코리아.com/p?q#f'),
                'base_uri' => Http::new('//xn--oy2b35ckwhba574atvuzkc.com/p?q#z'),
                'infos' => [
                    'absolute_uri' => false,
                    'network_path' => true,
                    'absolute_path' => false,
                    'relative_path' => false,
                    'same_document' => true,
                ],
            ],
            'path relative uri with non empty path' => [
                'uri' => Http::new('p?q#f'),
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
                'uri' => Http::new('?q#f'),
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

    public function testIsFunctionsThrowsTypeError(): void
    {
        self::assertTrue(UriInfo::isAbsolute('http://example.com'));
        self::assertFalse(UriInfo::isNetworkPath('http://example.com'));
        self::assertTrue(UriInfo::isAbsolutePath('/example.com'));
        self::assertTrue(UriInfo::isRelativePath('example.com#foobar'));
    }

    /**
     * @dataProvider sameValueAsProvider
     */
    public function testSameValueAs(Psr7UriInterface|Uri $uri1, Psr7UriInterface|Uri $uri2, bool $expected): void
    {
        self::assertSame($expected, UriInfo::isSameDocument($uri1, $uri2));
    }

    public static function sameValueAsProvider(): array
    {
        return [
            '2 disctincts URIs' => [
                Http::new('http://example.com'),
                Uri::new('ftp://example.com'),
                false,
            ],
            '2 identical URIs' => [
                Http::new('http://example.com'),
                Http::new('http://example.com'),
                true,
            ],
            '2 identical URIs after removing dot segment' => [
                Http::new('http://example.org/~foo/'),
                Http::new('http://example.ORG/bar/./../~foo/'),
                true,
            ],
            '2 distincts relative URIs' => [
                Http::new('~foo/'),
                Http::new('../~foo/'),
                false,
            ],
            '2 identical relative URIs' => [
                Http::new('../%7efoo/'),
                Http::new('../~foo/'),
                true,
            ],
            '2 identical URIs after normalization (1)' => [
                Http::new('HtTp://مثال.إختبار:80/%7efoo/%7efoo/'),
                Http::new('http://xn--mgbh0fb.xn--kgbechtv/%7Efoo/~foo/'),
                true,
            ],
            '2 identical URIs after normalization (2)' => [
                Http::new('http://www.example.com'),
                Http::new('http://www.example.com/'),
                true,
            ],
            '2 identical URIs after normalization (3)' => [
                Http::new('http://www.example.com'),
                Http::new('http://www.example.com:/'),
                true,
            ],
            '2 identical URIs after normalization (4)' => [
                Http::new('http://www.example.com'),
                Http::new('http://www.example.com:80/'),
                true,
            ],
        ];
    }

    /**
     * @dataProvider getOriginProvider
     */
    public function testGetOrigin(Psr7UriInterface|Uri $uri, ?string $expectedOrigin): void
    {
        self::assertSame($expectedOrigin, UriInfo::getOrigin($uri));
    }

    public static function getOriginProvider(): array
    {
        return [
            'http uri' => [
                'uri' => Uri::new('https://example.com/path?query#fragment'),
                'expectedOrigin' => 'https://example.com',
            ],
            'http uri with non standard port' => [
                'uri' => Uri::new('https://example.com:81/path?query#fragment'),
                'expectedOrigin' => 'https://example.com:81',
            ],
            'relative uri' => [
                'uri' => Uri::new('//example.com:81/path?query#fragment'),
                'expectedOrigin' => null,
            ],
            'absolute uri with user info' => [
                'uri' => Uri::new('https://user:pass@example.com:81/path?query#fragment'),
                'expectedOrigin' => 'https://example.com:81',
            ],
            'opaque URI' => [
                'uri' => Uri::new('mailto:info@thephpleague.com'),
                'expectedOrigin' => null,
            ],
            'file URI' => [
                'uri' => Uri::new('file:///usr/bin/test'),
                'expectedOrigin' => null,
            ],
            'blob' => [
                'uri' => Uri::new('blob:https://mozilla.org:443/'),
                'expectedOrigin' => 'https://mozilla.org',
            ],
        ];
    }

    /**
     * @dataProvider getCrossOriginExamples
     */
    public function testIsCrossOrigin(string $original, string $modified, bool $expected): void
    {
        self::assertSame($expected, UriInfo::isCrossOrigin(Uri::new($original), Http::new($modified)));
    }

    /**
     * @return array<string, array{0:string, 1:string, 2:bool}>
     */
    public static function getCrossOriginExamples(): array
    {
        return [
            'different path' => ['http://example.com/123', 'http://example.com/', false],
            'same port with default value (1)' => ['https://example.com/123', 'https://example.com:443/', false],
            'same port with default value (2)' => ['ws://example.com:80/123', 'ws://example.com/', false],
            'same explicit port' => ['wss://example.com:443/123', 'wss://example.com:443/', false],
            'same origin with i18n host' => ['https://xn--bb-bjab.be./path', 'https://Bébé.BE./path', false],
            'same origin using a blob' => ['blob:https://mozilla.org:443/', 'https://mozilla.org/123', false],
            'different scheme' => ['https://example.com/123', 'ftp://example.com/', true],
            'different host' => ['ftp://example.com/123', 'ftp://www.example.com/123', true],
            'different port implicit' => ['https://example.com/123', 'https://example.com:81/', true],
            'different port explicit' => ['https://example.com:80/123', 'https://example.com:81/', true],
            'same scheme different port' => ['https://example.com:443/123', 'https://example.com:444/', true],
            'comparing two opaque URI' => ['ldap://ldap.example.net', 'ldap://ldap.example.net', true],
            'comparing a URI with an origin and one with an opaque origin' => ['https://example.com:443/123', 'ldap://ldap.example.net', true],
            'cross origin using a blob' => ['blob:http://mozilla.org:443/', 'https://mozilla.org/123', true],
        ];
    }
}
