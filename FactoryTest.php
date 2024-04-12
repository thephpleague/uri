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

use League\Uri\Exceptions\SyntaxError;
use Nyholm\Psr7\Uri as NyholmUri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Stringable;

#[CoversClass(Uri::class)]
#[Group('factory')]
final class FactoryTest extends TestCase
{
    #[DataProvider('invalidDataPath')]
    public function testCreateFromPathFailed(string $path): void
    {
        self::expectException(SyntaxError::class);
        Uri::fromFileContents($path);
    }

    public static function invalidDataPath(): array
    {
        return [
            'invalid format' => ['/usr/bin/yeah'],
        ];
    }

    #[DataProvider('validFilePath')]
    public function testCreateFromPath(string $path, string $expected): void
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Accept-language: en\r\nCookie: foo=bar\r\n",
            ],
        ]);

        $uri = Uri::fromFileContents(dirname(__DIR__).'/test_files/'.$path, $context);
        self::assertStringContainsString($expected, $uri->getPath());
    }

    public static function validFilePath(): array
    {
        return [
            'text file' => ['hello-world.txt', 'text/plain'],
            'img file' => ['red-nose.gif', 'image/gif'],
        ];
    }

    #[DataProvider('provideValidData')]
    public function testFromData(string $data, string $mimetype, string $parameters, string $expected): void
    {
        self::assertSame($expected, Uri::fromData($data, $mimetype, $parameters)->toString());
    }

    public static function provideValidData(): iterable
    {
        yield 'empty data' => [
            'data' => '',
            'mimetype' => '',
            'parameters' => '',
            'expected' => 'data:text/plain;charset=us-ascii,',
        ];

        yield 'simple string' => [
            'data' => 'Hello World!',
            'mimetype' => '',
            'parameters' => '',
            'expected' => 'data:text/plain;charset=us-ascii,Hello%20World%21',
        ];

        yield 'changing the mimetype' => [
            'data' => 'Hello World!',
            'mimetype' => 'text/no-plain',
            'parameters' => '',
            'expected' => 'data:text/no-plain;charset=us-ascii,Hello%20World%21',
        ];

        yield 'empty mimetype but added parameters' => [
            'data' => 'Hello World!',
            'mimetype' => '',
            'parameters' => 'foo=bar',
            'expected' => 'data:text/plain;foo=bar,Hello%20World%21',
        ];

        yield 'empty data with optional argument field' => [
            'data' => '',
            'mimetype' => 'application/json',
            'parameters' => 'foo=bar',
            'expected' => 'data:application/json;foo=bar,',
        ];

        yield 'changing the parameters' => [
            'data' => 'Hello World!',
            'mimetype' => 'text/no-plain',
            'parameters' => 'foo=bar',
            'expected' => 'data:text/no-plain;foo=bar,Hello%20World%21',
        ];

        yield 'the parameters can start with the ; character' => [
            'data' => 'Hello World!',
            'mimetype' => 'text/no-plain',
            'parameters' => ';foo=bar',
            'expected' => 'data:text/no-plain;foo=bar,Hello%20World%21',
        ];
    }

    public function testFromDataFailsWithInvalidMimeType(): void
    {
        $this->expectException(SyntaxError::class);

        Uri::fromData('Hello World!', 'text\plain');
    }

    public function testFromDataFailsWithReservedParameterName(): void
    {
        $this->expectException(SyntaxError::class);

        Uri::fromData('Hello World!', 'text/plain', 'base64=foobar');
    }

    public function testFromDataFailsWithMalformedParameters(): void
    {
        $this->expectException(SyntaxError::class);

        Uri::fromData('Hello World!', 'text/plain', 'foobar');
    }

    #[DataProvider('unixpathProvider')]
    public function testCreateFromUnixPath(string $input, string $expected): void
    {
        self::assertSame($expected, Uri::fromUnixPath($input)->toString());
    }

    public static function unixpathProvider(): array
    {
        return [
            'relative path' => [
                'input' => 'path',
                'expected' => 'path',
            ],
            'absolute path' => [
                'input' => '/path',
                'expected' => 'file:///path',
            ],
            'path with empty char' => [
                'input' => '/path empty/bar',
                'expected' => 'file:///path%20empty/bar',
            ],
            'relative path with dot segments' => [
                'input' => 'path/./relative',
                'expected' => 'path/./relative',
            ],
            'absolute path with dot segments' => [
                'input' => '/path/./../relative',
                'expected' => 'file:///path/./../relative',
            ],
        ];
    }

    #[DataProvider('windowLocalPathProvider')]
    public function testCreateFromWindowsLocalPath(string $input, string $expected): void
    {
        self::assertSame($expected, Uri::fromWindowsPath($input)->toString());
    }

    public static function windowLocalPathProvider(): array
    {
        return [
            'relative path' => [
                'input' => 'path',
                'expected' => 'path',
            ],
            'relative path with dot segments' => [
                'input' => 'path\.\relative',
                'expected' => 'path/./relative',
            ],
            'absolute path' => [
                'input' => 'c:\windows\My Documents 100%20\foo.txt',
                'expected' => 'file:///c:/windows/My%20Documents%20100%2520/foo.txt',
            ],
            'windows relative path' => [
                'input' => 'c:My Documents 100%20\foo.txt',
                'expected' => 'file:///c:My%20Documents%20100%2520/foo.txt',
            ],
            'absolute path with `|`' => [
                'input' => 'c|\windows\My Documents 100%20\foo.txt',
                'expected' => 'file:///c:/windows/My%20Documents%20100%2520/foo.txt',
            ],
            'windows relative path with `|`' => [
                'input' => 'c:My Documents 100%20\foo.txt',
                'expected' => 'file:///c:My%20Documents%20100%2520/foo.txt',
            ],
            'absolute path with dot segments' => [
                'input' => '\path\.\..\relative',
                'expected' => '/path/./../relative',
            ],
            'absolute UNC path' => [
                'input' => '\\\\server\share\My Documents 100%20\foo.txt',
                'expected' => 'file://server/share/My%20Documents%20100%2520/foo.txt',
            ],
        ];
    }

    #[DataProvider('rfc8089UriProvider')]
    public function testCreateFromRfc8089(string $expected, string $uri): void
    {
        self::assertSame($expected, Uri::fromRfc8089($uri)->toString());
    }

    public static function rfc8089UriProvider(): iterable
    {
        return [
            'empty authority' => [
                'expected' => 'file:///etc/fstab',
                'uri' => 'file:/etc/fstab',
            ],
            'localhost' => [
                'expected' => 'file:///etc/fstab',
                'uri' => 'file://localhost/etc/fstab',
            ],
            'file with authority' => [
                'expected' => 'file://yesman/etc/fstab',
                'uri' => 'file://yesman/etc/fstab',
            ],
        ];
    }

    #[DataProvider('invalidRfc8089UriProvider')]
    public function testIfFailsToGenerateAnUriFromRfc8089(string $invalidUri): void
    {
        $this->expectException(SyntaxError::class);

        Uri::fromRfc8089($invalidUri);
    }

    public static function invalidRfc8089UriProvider(): iterable
    {
        return [
            'unsupported scheme' => [
                'invalidUri' => 'http://www.example.com',
            ],
            'missing scheme' => [
                'invalidUri' => '//localhost/etc/fstab',
            ],
        ];
    }

    public function testCreateFromUri(): void
    {
        $expected = 'https://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3';
        $psr7 = Http::new($expected);
        $leagueUri = Uri::new($expected);

        $uriFromPsr7 = Uri::new($psr7);
        $uriFromLeagueUri = Uri::new($leagueUri);

        self::assertSame((string) $psr7, $uriFromPsr7->toString());
        self::assertSame((string) $psr7, $uriFromLeagueUri->toString());

        $uribis = Http::new();
        self::assertSame((string) $uribis, Uri::new($uribis)->toString());
    }

    #[DataProvider('validServerArray')]
    public function testCreateFromServer(string $expected, array $input): void
    {
        self::assertSame($expected, Uri::fromServer($input)->toString());
        self::assertSame($expected, (string) Http::fromServer($input));
    }

    public static function validServerArray(): array
    {
        return [
            'with host' => [
                'https://example.com:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => 'on',
                    'SERVER_PORT' => '23',
                    'HTTP_HOST' => 'example.com',
                ],
            ],
            'server address IPv4' => [
                'https://127.0.0.1:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 23,
                ],
            ],
            'server address IPv6' => [
                'https://[::1]:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '::1',
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 23,
                ],
            ],
            'with port attached to host' => [
                'https://localhost:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 80,
                    'HTTP_HOST' => 'localhost:23',
                ],
            ],
            'with standard apache HTTP server' => [
                'http://localhost:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => '',
                    'SERVER_PORT' => 80,
                    'HTTP_HOST' => 'localhost:23',
                ],
            ],
            'with IIS HTTP server' => [
                'http://localhost:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => 'off',
                    'SERVER_PORT' => 80,
                    'HTTP_HOST' => 'localhost:23',
                ],
            ],
            'with IIS Rewritting server' => [
                'http://localhost:23/foo/bar?foo=bar',
                [
                    'PHP_SELF' => '',
                    'IIS_WasUrlRewritten' => '1',
                    'UNENCODED_URL' => '/foo/bar?foo=bar',
                    'REQUEST_URI' => 'toto',
                    'SERVER_PORT' => 23,
                    'HTTP_HOST' => 'localhost',
                ],
            ],
            'with standard port setting' => [
                'https://localhost:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 23,
                    'HTTP_HOST' => 'localhost',
                ],
            ],
            'without port' => [
                'https://localhost',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => 'on',
                    'HTTP_HOST' => 'localhost',
                ],
            ],
            'with user info' => [
                'https://foo:bar@localhost:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'PHP_AUTH_USER' => 'foo',
                    'PHP_AUTH_PW' => 'bar',
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 23,
                    'HTTP_HOST' => 'localhost:23',
                ],
            ],
            'with user info and HTTP AUTHORIZATION' => [
                'https://foo:bar@localhost:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTP_AUTHORIZATION' => 'basic '.base64_encode('foo:bar'),
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 23,
                    'HTTP_HOST' => 'localhost:23',
                ],
            ],
            'without request uri' => [
                'https://127.0.0.1:23/toto?foo=bar',
                [
                    'PHP_SELF' => '/toto',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 23,
                    'QUERY_STRING' => 'foo=bar',
                ],
            ],
            'without request uri and server host' => [
                'https://127.0.0.1:23',
                [
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 23,
                ],
            ],
        ];
    }

    public function testFailCreateFromServerWithoutHost(): void
    {
        self::expectException(SyntaxError::class);
        Uri::fromServer([
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'HTTPS' => 'on',
            'SERVER_PORT' => 23,
        ]);
    }

    public function testFailCreateFromServerWithoutInvalidUserInfo(): void
    {
        self::expectException(SyntaxError::class);
        Uri::fromServer([
            'PHP_SELF' => '/toto',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PORT' => 23,
            'QUERY_STRING' => 'foo=bar',
            'HTTP_AUTHORIZATION' => 'basic foo:bar',
        ]);
    }

    #[DataProvider('createProvider')]
    public function testCreateFromBaseUri(Stringable|string $baseUri, Stringable|string $uri, string $expected): void
    {
        self::assertSame($expected, Uri::fromBaseUri($uri, $baseUri)->toString());
    }

    public static function createProvider(): array
    {
        $base_uri = 'http://a/b/c/d;p?q';

        return [
            'base uri'                => [$base_uri, '',              $base_uri],
            'scheme'                  => [$base_uri, 'http://d/e/f',  'http://d/e/f'],
            'path 1'                  => [$base_uri, 'g',             'http://a/b/c/g'],
            'path 2'                  => [$base_uri, './g',           'http://a/b/c/g'],
            'path 3'                  => [$base_uri, 'g/',            'http://a/b/c/g/'],
            'path 4'                  => [$base_uri, '/g',            'http://a/g'],
            'authority'               => [$base_uri, '//g',           'http://g'],
            'query'                   => [$base_uri, '?y',            'http://a/b/c/d;p?y'],
            'path + query'            => [$base_uri, 'g?y',           'http://a/b/c/g?y'],
            'fragment'                => [$base_uri, '#s',            'http://a/b/c/d;p?q#s'],
            'path + fragment'         => [$base_uri, 'g#s',           'http://a/b/c/g#s'],
            'path + query + fragment' => [$base_uri, 'g?y#s',         'http://a/b/c/g?y#s'],
            'single dot 1'            => [$base_uri, '.',             'http://a/b/c/'],
            'single dot 2'            => [$base_uri, './',            'http://a/b/c/'],
            'single dot 3'            => [$base_uri, './g/.',         'http://a/b/c/g/'],
            'single dot 4'            => [$base_uri, 'g/./h',         'http://a/b/c/g/h'],
            'double dot 1'            => [$base_uri, '..',            'http://a/b/'],
            'double dot 2'            => [$base_uri, '../',           'http://a/b/'],
            'double dot 3'            => [$base_uri, '../g',          'http://a/b/g'],
            'double dot 4'            => [$base_uri, '../..',         'http://a/'],
            'double dot 5'            => [$base_uri, '../../',        'http://a/'],
            'double dot 6'            => [$base_uri, '../../g',       'http://a/g'],
            'double dot 7'            => [$base_uri, '../../../g',    'http://a/g'],
            'double dot 8'            => [$base_uri, '../../../../g', 'http://a/g'],
            'double dot 9'            => [$base_uri, 'g/../h' ,       'http://a/b/c/h'],
            'mulitple slashes'        => [$base_uri, 'foo////g',      'http://a/b/c/foo////g'],
            'complex path 1'          => [$base_uri, ';x',            'http://a/b/c/;x'],
            'complex path 2'          => [$base_uri, 'g;x',           'http://a/b/c/g;x'],
            'complex path 3'          => [$base_uri, 'g;x?y#s',       'http://a/b/c/g;x?y#s'],
            'complex path 4'          => [$base_uri, 'g;x=1/./y',     'http://a/b/c/g;x=1/y'],
            'complex path 5'          => [$base_uri, 'g;x=1/../y',    'http://a/b/c/y'],
            'dot segments presence 1' => [$base_uri, '/./g',          'http://a/g'],
            'dot segments presence 2' => [$base_uri, '/../g',         'http://a/g'],
            'dot segments presence 3' => [$base_uri, 'g.',            'http://a/b/c/g.'],
            'dot segments presence 4' => [$base_uri, '.g',            'http://a/b/c/.g'],
            'dot segments presence 5' => [$base_uri, 'g..',           'http://a/b/c/g..'],
            'dot segments presence 6' => [$base_uri, '..g',           'http://a/b/c/..g'],
            'origin uri without path' => ['http://h:b@a', 'b/../y',   'http://h:b@a/y'],
            'uri without auhtority'   => ['mailto:f@a.b', 'b@c.d?subject=baz', 'mailto:b@c.d?subject=baz'],
            'with PSR7 base URI' => [new NyholmUri($base_uri), 'g.', 'http://a/b/c/g.'],
            'with PSR7 ref URI' => [$base_uri, new NyholmUri('g.'), 'http://a/b/c/g.'],
            'with PSR7 base URI and ref URI' => [new NyholmUri($base_uri), new NyholmUri('g.'), 'http://a/b/c/g.'],
        ];
    }

    public function testCreateThrowExceptionWithBaseUriNotAbsolute(): void
    {
        self::expectException(SyntaxError::class);
        Uri::fromBaseUri('/path/to/you', '//example.com');
    }

    public function testCreateThrowExceptionWithUriNotAbsolute(): void
    {
        self::expectException(SyntaxError::class);
        Uri::fromBaseUri('/path/to/you');
    }

    public function testCreateWithUriWithoutAuthority(): void
    {
        self::assertSame(
            'data:text/plain;charset=us-ascii,',
            Uri::fromBaseUri('data:text/plain;charset=us-ascii,')->toString()
        );
    }

    public function testCreateWithAbsoluteUriWithoutBaseUri(): void
    {
        self::assertSame(
            'scheme://host/sky?q#f',
            Uri::fromBaseUri('scheme://host/path/../sky?q#f')->toString()
        );
    }

    public function testCreateFromComponentsWithNullPath(): void
    {
        self::assertSame('', Uri::fromComponents(['path' => null])->toString());
    }

    public function testICanBeInstantiateFromRFC6750(): void
    {
        $template = 'https://example.com/hotels/{hotel}/bookings/{booking}';
        $params = ['booking' => '42', 'hotel' => 'Rest & Relax'];

        self::assertSame(
            '/hotels/Rest%20%26%20Relax/bookings/42',
            Uri::fromTemplate($template, $params)->getPath()
        );
    }
}
