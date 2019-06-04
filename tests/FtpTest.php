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
 * @group ftp
 * @group uri
 * @coversDefaultClass League\Uri\Uri
 */
class FtpTest extends TestCase
{
    /**
     * @dataProvider validUrlProvider
     */
    public function testCreateFromString(string $uri, string $expected): void
    {
        self::assertSame($expected, (string) Uri::createFromString($uri));
    }

    public function validUrlProvider(): array
    {
        return [
            'with default port' => [
                'FtP://ExAmpLe.CoM:21/foo/bar',
                'ftp://example.com/foo/bar',
            ],
            'with user info' => [
                'ftp://login:pass@example.com/',
                'ftp://login:pass@example.com/',
            ],
            'with network path' => [
                '//ExAmpLe.CoM:80',
                '//example.com:80',
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

     *
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
            //['http://example.com'],
            ['ftp:/example.com'],
            ['ftp:example.com'],
            ['ftp://example.com?query#fragment'],
        ];
    }

    public function testModificationFailedWithEmptyAuthority(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString('ftp://example.com/path')
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
            ['ftp://www.example.com:443/', 443],
            ['ftp://www.example.com:21/', null],
            ['ftp://www.example.com', null],
            ['//www.example.com:21/', 21],
        ];
    }
}
