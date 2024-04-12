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

use InvalidArgumentException;
use League\Uri\Exceptions\SyntaxError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

#[CoversClass(Http::class)]
#[Group('http')]
final class HttpTest extends TestCase
{
    public function createUri(string $uri): UriInterface
    {
        return (new HttpFactory())->createUri($uri);
    }

    public function testDefaultConstructor(): void
    {
        self::assertSame('', (string) Http::new());
    }

    public function testJson(): void
    {
        self::assertSame(
            '"http:\/\/example.com"',
            json_encode(Http::new('http://example.com'))
        );
    }

    public function testInvalidPort(): void
    {
        self::expectException(InvalidArgumentException::class);

        Http::new('https://example.com:-1');
    }

    public function testThrowInvalidArgumentExceptionOnIllegalCharacters(): void
    {
        self::expectException(InvalidArgumentException::class);
        Http::new('https://example.com')->withFragment("\0");
    }

    public function testPortModification(): void
    {
        $uri = Http::new('http://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3');
        self::assertSame(443, $uri->getPort());
        self::assertSame($uri, $uri->withPort(443));
        self::assertNotEquals($uri, $uri->withPort(81));
        self::assertSame(
            'http://login:pass@secure.example.com/test/query.php?kingkong=toto#doc3',
            (string) $uri->withPort(null)
        );
    }

    public function testUserInfoModification(): void
    {
        $uri = Http::new('http://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3');
        self::assertSame('login:pass', $uri->getUserInfo());
        self::assertSame($uri, $uri->withUserInfo('login', 'pass'));
        self::assertNotEquals($uri, $uri->withUserInfo('login'));
        self::assertSame(
            'http://secure.example.com:443/test/query.php?kingkong=toto#doc3',
            (string) $uri->withUserInfo('')
        );
    }

    public function testCreateFromComponents(): void
    {
        $uri = '//0:0@0/0?0#0';
        self::assertEquals(
            Http::fromComponents(parse_url($uri)),
            Http::new($uri)
        );
    }

    public function testCreateFromBaseUri(): void
    {
        self::assertEquals(
            Http::new('http://0:0@0.0.0.0/0?0#0'),
            Http::fromBaseUri('0?0#0', 'http://0:0@0/')
        );
    }

    public function testCreateFromUri(): void
    {
        self::assertEquals(
            Http::new('http://0:0@0/0?0#0'),
            Http::new(Uri::new('http://0:0@0/0?0#0'))
        );

        self::assertEquals(
            Http::new('http://0:0@0/0?0#0'),
            Http::new(Http::new('http://0:0@0/0?0#0'))
        );
    }

    #[DataProvider('validUrlProvider')]
    public function testCreateFromString(string $expected, string $uri): void
    {
        self::assertSame($expected, (string) Http::new($uri));
    }

    public static function validUrlProvider(): array
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

    #[DataProvider('invalidUrlProvider')]
    public function testIsValid(string $uri): void
    {
        self::expectException(SyntaxError::class);
        Http::new($uri);
    }

    public static function invalidUrlProvider(): array
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

    #[DataProvider('portProvider')]
    public function testValidPort(string $uri, ?int $port): void
    {
        self::assertSame($port, Http::new($uri)->getPort());
    }

    public static function portProvider(): array
    {
        return [
            ['http://www.example.com:443/', 443],
            ['http://www.example.com:80/', null],
            ['http://www.example.com', null],
            ['//www.example.com:80/', 80],
        ];
    }

    #[DataProvider('invalidPathProvider')]
    public function testPathIsInvalid(string $path): void
    {
        self::expectException(SyntaxError::class);

        Http::new()->withPath($path);
    }

    public static function invalidPathProvider(): array
    {
        return [
            ['data:go'],
            ['//data'],
            ['to://to'],
        ];
    }

    #[DataProvider('invalidURI')]
    public function testCreateFromInvalidUrlKO(string $uri): void
    {
        self::expectException(SyntaxError::class);

        Http::new($uri);
    }

    public static function invalidURI(): array
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

        Http::new('http://example.com/path')
            ->withScheme('')
            ->withHost('')
            ->withPath('//toto');
    }

    public function testItStripMultipleLeadingSlashOnGetPath(): void
    {
        $uri = Http::new('https://example.com///miscillaneous.tld');

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
        $uri = Http::new('https://www.example.com///google.com');

        self::assertSame('https://www.example.com///google.com', (string) $uri);
        self::assertSame('/google.com', $uri->getPath());

        $modifiedUri =  $uri->withPath('/google.com');

        self::assertSame('https://www.example.com/google.com', (string) $modifiedUri);
        self::assertSame('/google.com', $modifiedUri->getPath());

        $modifiedUri2 =  $uri->withPath('///google.com');

        self::assertSame('https://www.example.com///google.com', (string) $modifiedUri2);
        self::assertSame('/google.com', $modifiedUri2->getPath());
    }

    public function testICanBeInstantiateFromRFC6750(): void
    {
        $template = 'https://example.com/hotels/{hotel}/bookings/{booking}';
        $params = ['booking' => '42', 'hotel' => 'Rest & Relax'];

        self::assertSame(
            '/hotels/Rest%20%26%20Relax/bookings/42',
            Http::fromTemplate($template, $params)->getPath()
        );
    }
}
