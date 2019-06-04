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

use League\Uri\Exceptions\SyntaxError;
use League\Uri\Uri;
use PHPUnit\Framework\TestCase;

/**
 * @group ws
 * @group uri
 * @coversDefaultClass League\Uri\Uri
 */
class WsTest extends TestCase
{
    /**
     *
     * @dataProvider validUrlProvider
     */
    public function testCreateFromString(string $input, string $expected): void
    {
        self::assertSame($expected, (string) Uri::createFromString($input));
    }

    public function validUrlProvider(): array
    {
        return [
            'with default port' => [
                'Ws://ExAmpLe.CoM:80/foo/bar?foo=bar',
                'ws://example.com/foo/bar?foo=bar',
            ],
            'with user info' => [
                'wss://login:pass@example.com/',
                'wss://login:pass@example.com/',
            ],
            'network path' => [
                '//ExAmpLe.CoM:21',
                '//example.com:21',
            ],
            'absolute path' => [
                '/path/to/my/file',
                '/path/to/my/file',
            ],
            'relative path' => [
                '.././path/../is/./relative',
                '.././path/../is/./relative',
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
    public function testConstructorThrowInvalidArgumentException(string $uri): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString($uri);
    }

    public function invalidUrlProvider(): array
    {
        return [
            ['wss:example.com'],
            ['wss:/example.com'],
            ['wss://example.com:80/foo/bar?foo=bar#content'],
        ];
    }

    public function testModificationFailedWithEmptyAuthority(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString('wss://example.com/path')
            ->withScheme(null)
            ->withHost(null)
            ->withPath('//toto');
    }

    /**
     * @dataProvider portProvider
     * @param ?int $port
     */
    public function testPort(string $uri, ?int $port): void
    {
        self::assertSame($port, Uri::createFromString($uri)->getPort());
    }

    public function portProvider(): array
    {
        return [
            ['ws://www.example.com:443/', 443],
            ['ws://www.example.com:80/', null],
            ['ws://www.example.com', null],
            ['//www.example.com:80/', 80],
        ];
    }
}
