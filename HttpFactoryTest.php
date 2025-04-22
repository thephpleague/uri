<?php

/**
 * League.Uri (https://uri.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Uri;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriFactoryInterface;

final class HttpFactoryTest extends TestCase
{
    protected function createUriFactory(): UriFactoryInterface
    {
        return new HttpFactory();
    }

    public function testCreateUri(): void
    {
        $uri = $this->createUriFactory()->createUri('https://nyholm.tech/foo');

        self::assertInstanceOf(Http::class, $uri);
        self::assertEquals('https://nyholm.tech/foo', $uri->__toString());
    }

    #[Test]
    #[DataProvider('invalidUriWithWhitespaceProvider')]
    public function it_fails_parsing_uri_string_with_whitespace(string $uri): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createUriFactory()->createUri($uri);
    }

    public static function invalidUriWithWhitespaceProvider(): iterable
    {
        yield 'uri containing only whitespaces' => ['uri' => '     '];
        yield 'uri starting with whitespaces' => ['uri' => '    https://a/b?c'];
        yield 'uri ending with whitespaces' => ['uri' => 'https://a/b?c   '];
        yield 'uri surrounded with whitespaces' => ['uri' => '   https://a/b?c   '];
    }
}
