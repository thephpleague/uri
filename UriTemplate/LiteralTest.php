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

namespace League\Uri\UriTemplate;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LiteralTest extends TestCase
{
    #[Test]
    #[DataProvider('provideLiterals')]
    public function it_encodes_literal(string $raw, string $expected): void
    {
        $literal = new Literal($raw);

        self::assertSame($raw, $literal->raw);
        self::assertSame($expected, $literal->encoded);
    }

    /**
     * @return iterable<non-empty-string, array<string>>
     */
    public static function provideLiterals(): iterable
    {
        yield 'trimmed empty string' => ['', ''];

        yield 'unreserved characters' => [
            'abc-XYZ012._~',
            'abc-XYZ012._~',
        ];

        yield 'reserved characters' => [
            ':/?#[]@!$&\'()*+,;=',
            ':/?#[]@!$&\'()*+,;=',
        ];

        yield 'spaces are encoded' => [
            'hello world',
            'hello%20world',
        ];

        yield 'unicode characters are encoded' => [
            'café',
            'caf%C3%A9',
        ];

        yield 'existing percent encoding is preserved' => [
            'hello%20world',
            'hello%20world',
        ];

        yield 'invalid percent encoding is encoded' => [
            'hello%world',
            'hello%25world',
        ];

        yield 'mixed literal' => [
            'https://example.com/foo bar?q=hello world#section',
            'https://example.com/foo%20bar?q=hello%20world#section',
        ];

        yield 'empty string' => [
            '        ',
            '%20%20%20%20%20%20%20%20',
        ];
    }
}
