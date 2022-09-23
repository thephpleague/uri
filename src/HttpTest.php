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

use Http\Psr7Test\UriIntegrationTest;
use InvalidArgumentException;
use League\Uri\Exceptions\SyntaxError;
use Psr\Http\Message\UriInterface;

/**
 * @group http
 * @coversDefaultClass \League\Uri\Http
 */
final class HttpTest extends UriIntegrationTest
{
    public function createUri($uri): UriInterface
    {
        return (new HttpFactory())->createUri($uri);
    }

    public function testDefaultConstructor(): void
    {
        self::assertSame('', (string) Http::createFromString());
    }

    /**
     * @covers ::jsonSerialize
     */
    public function testJson(): void
    {
        self::assertSame(
            '"http:\/\/example.com"',
            json_encode(Http::createFromString('http://example.com'))
        );
    }

    /**
     * @covers ::__construct
     */
    public function testInvalidPort(): void
    {
        self::expectException(InvalidArgumentException::class);

        Http::createFromString('https://example.com:-1');
    }

    /**
     * @covers ::filterInput
     */
    public function testThrowTypeErrorOnWrongType(): void
    {
        self::expectException(InvalidArgumentException::class);

        Http::createFromString('https://example.com')->withFragment([]); /* @phpstan-ignore-line */
    }

    /**
     * @covers ::filterInput
     */
    public function testThrowInvalidArgumentExceptionOnIllegalCharacters(): void
    {
        self::expectException(InvalidArgumentException::class);
        Http::createFromString('https://example.com')->withFragment("\0");
    }

    /**
     * @covers ::filterInput
     */
    public function testAcceptsNullQuery(): void
    {
        self::assertSame(
            'https://example.com',
            (string)Http::createFromString('https://example.com')->withQuery(null) /* @phpstan-ignore-line */
        );
    }

    /**
     * @covers ::getPort
     * @covers ::withPort
     */
    public function testPortModification(): void
    {
        $uri = Http::createFromString('http://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3');
        self::assertSame(443, $uri->getPort());
        self::assertSame($uri, $uri->withPort(443));
        self::assertNotEquals($uri, $uri->withPort(81));
        self::assertSame(
            'http://login:pass@secure.example.com/test/query.php?kingkong=toto#doc3',
            (string) $uri->withPort(null)
        );
    }

    /**
     * @covers ::getUserInfo
     * @covers ::withUserInfo
     */
    public function testUserInfoModification(): void
    {
        $uri = Http::createFromString('http://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3');
        self::assertSame('login:pass', $uri->getUserInfo());
        self::assertSame($uri, $uri->withUserInfo('login', 'pass'));
        self::assertNotEquals($uri, $uri->withUserInfo('login'));
        self::assertSame(
            'http://secure.example.com:443/test/query.php?kingkong=toto#doc3',
            (string) $uri->withUserInfo('')
        );
    }

    /**
     * @covers ::createFromComponents
     */
    public function testCreateFromComponents(): void
    {
        $uri = '//0:0@0/0?0#0';
        self::assertEquals(
            Http::createFromComponents(parse_url($uri)),
            Http::createFromString($uri)
        );
    }

    /**
     * @covers ::createFromBaseUri
     */
    public function testCreateFromBaseUri(): void
    {
        self::assertEquals(
            Http::createFromString('http://0:0@0/0?0#0'),
            Http::createFromBaseUri('0?0#0', 'http://0:0@0/')
        );
    }

    /**
     * @covers ::createFromUri
     */
    public function testCreateFromUri(): void
    {
        self::assertEquals(
            Http::createFromString('http://0:0@0/0?0#0'),
            Http::createFromUri(Uri::createFromString('http://0:0@0/0?0#0'))
        );

        self::assertEquals(
            Http::createFromString('http://0:0@0/0?0#0'),
            Http::createFromUri(Http::createFromString('http://0:0@0/0?0#0'))
        );
    }

    /**
     * @dataProvider setStateDataProvider
     *
     * @covers ::__set_state
     */
    public function testSetState(Http $uri): void
    {
        self::assertEquals($uri, eval('return '.var_export($uri, true).';'));
    }

    public function setStateDataProvider(): array
    {
        return [
            'all components' => [Http::createFromString('https://a:b@c:442/d?q=r#f')],
            'without scheme' => [Http::createFromString('//a:b@c:442/d?q=r#f')],
            'without userinfo' => [Http::createFromString('https://c:442/d?q=r#f')],
            'without port' => [Http::createFromString('https://a:b@c/d?q=r#f')],
            'without path' => [Http::createFromString('https://a:b@c:442?q=r#f')],
            'without query' => [Http::createFromString('https://a:b@c:442/d#f')],
            'without fragment' => [Http::createFromString('https://a:b@c:442/d?q=r')],
            'without pass' => [Http::createFromString('https://a@c:442/d?q=r#f')],
            'without authority' => [Http::createFromString('/d?q=r#f')],
       ];
    }

    /**
     * @covers \League\Uri\Uri::formatPort
     *
     * @dataProvider validUrlProvider
     */
    public function testCreateFromString(string $expected, string $uri): void
    {
        self::assertSame($expected, (string) Http::createFromString($uri));
    }

    public function validUrlProvider(): array
    {
        return [
            'with default port' => [
                'http://example.com/foo/bar?foo=bar#content',
                'http://example.com:80/foo/bar?foo=bar#content',
            ],
            'without scheme' => [
                '//example.com',
                '//example.com',
            ],
            'without scheme but with port' => [
                '//example.com:80',
                '//example.com:80',
            ],
            'with user info' => [
                'http://login:pass@example.com/',
                'http://login:pass@example.com/',
            ],
            'empty string' => [
                '',
                '',
            ],
        ];
    }

    /**
     * @dataProvider invalidUrlProvider
     */
    public function testIsValid(string $uri): void
    {
        self::expectException(SyntaxError::class);
        Http::createFromString($uri);
    }

    public function invalidUrlProvider(): array
    {
        return [
            ['http:example.com'],
            ['https:/example.com'],
            ['http://user@:80'],
            ['//user@:80'],
            ['http:///path'],
            ['http:path'],
        ];
    }

    /**
     * @dataProvider portProvider
     *
     * @covers \League\Uri\Uri::formatPort
     */
    public function testValidPort(string $uri, ?int $port): void
    {
        self::assertSame($port, Http::createFromString($uri)->getPort());
    }

    public function portProvider(): array
    {
        return [
            ['http://www.example.com:443/', 443],
            ['http://www.example.com:80/', null],
            ['http://www.example.com', null],
            ['//www.example.com:80/', 80],
        ];
    }

    /**
     * @dataProvider invalidPathProvider
     */
    public function testPathIsInvalid(string $path): void
    {
        self::expectException(SyntaxError::class);

        Http::createFromString()->withPath($path);
    }

    public function invalidPathProvider(): array
    {
        return [
            ['data:go'],
            ['//data'],
            ['to://to'],
        ];
    }

    /**
     * @covers ::validate
     *
     * @dataProvider invalidURI
     */
    public function testCreateFromInvalidUrlKO(string $uri): void
    {
        self::expectException(SyntaxError::class);

        Http::createFromString($uri);
    }

    public function invalidURI(): array
    {
        return [
            ['http://user@:80'],
            ['http://example.com:655356'],
            ['http://example.com:-1'],
            ['///path?query'],
        ];
    }

    public function testModificationFailedWithEmptyAuthority(): void
    {
        self::expectException(SyntaxError::class);

        Http::createFromString('http://example.com/path')
            ->withScheme('')
            ->withHost('')
            ->withPath('//toto');
    }

    public function testItStripMultipleLeadingSlashOnGetPath(): void
    {
        $uri = Http::createFromString('https://example.com///miscillaneous.tld');

        self::assertSame('https://example.com///miscillaneous.tld', (string) $uri);
        self::assertSame('/miscillaneous.tld', $uri->getPath());

        $modifiedUri = $uri->withPath('///foobar');

        self::assertSame('https://example.com///foobar', (string) $modifiedUri);
        self::assertSame('/foobar', $modifiedUri->getPath());
        self::assertSame('//example.com///foobar', (string) $modifiedUri->withScheme(''));

        $this->expectException(SyntaxError::class);

        $modifiedUri->withScheme('')->withHost('');
    }

    public function testItPreservesMultipleLeadingSlashesOnMutation(): void
    {
        $uri = Http::createFromString('https://www.example.com///google.com');

        self::assertSame('https://www.example.com///google.com', (string) $uri);
        self::assertSame('/google.com', $uri->getPath());

        $modifiedUri =  $uri->withPath('/google.com');

        self::assertSame('https://www.example.com/google.com', (string) $modifiedUri);
        self::assertSame('/google.com', $modifiedUri->getPath());

        $modifiedUri2 =  $uri->withPath('///google.com');

        self::assertSame('https://www.example.com///google.com', (string) $modifiedUri2);
        self::assertSame('/google.com', $modifiedUri2->getPath());
    }
}
