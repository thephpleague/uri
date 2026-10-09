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

use League\Uri\Exceptions\SyntaxError;
use League\Uri\UrlPattern\Result;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UrlPatternTest extends TestCase
{
    #[Test]
    #[DataProvider('provideOptionalId')]
    public function it_extracts_optional_parameters(string $pattern, string $input, ?string $expected): void
    {
        $result = UrlPattern::from($pattern)->extract($input);

        self::assertInstanceOf(Result::class, $result);
        self::assertSame($expected, $result->path['id']);
        self::assertSame($expected, $result->path['id']);

    }

    public static function provideOptionalId(): iterable
    {
        yield 'missing' => ['/book/:id?', '/book', null];
        yield 'present' => ['/book/:id?', '/book/123', '123'];
    }

    #[Test]
    public function it_detects_a_regexp_group_for_regexp_parameter(): void
    {
        $pattern = UrlPatternBuilder::from('/book/:id?(\d+)')->build();

        self::assertTrue($pattern->hasRegexpGroup);
    }

    #[Test]
    public function it_does_not_detects_a_regexp_group_for_an_optional_parameter(): void
    {
        $pattern = UrlPatternBuilder::from('/book/:id?')->build();

        self::assertFalse($pattern->hasRegexpGroup);
        self::assertSame('/book/:id?', $pattern->path);
        self::assertSame('*', $pattern->fragment);
    }

    #[Test]
    public function it_does_not_detect_a_regexp_group_for_a_fixed_pattern(): void
    {
        $pattern = UrlPatternBuilder::from('/book/123')->build();

        self::assertFalse($pattern->hasRegexpGroup);
    }

    #[Test]
    public function it_matches_a_fixed_pattern(): void
    {
        $pattern = UrlPatternBuilder::from('/book/123')->build();

        self::assertTrue($pattern->match('/book/123'));
        self::assertFalse($pattern->match('/book/456'));

        $result = $pattern->extract('/book/123');

        self::assertInstanceOf(Result::class, $result);
        self::assertFalse($result->hasValue());
        self::assertFalse($pattern->hasVariable);
    }

    #[Test]
    public function it_extracts_a_named_path_parameter(): void
    {
        $pattern = UrlPatternBuilder::from('/users/:id')->build();
        $result = $pattern->extract('/users/42');

        self::assertInstanceOf(Result::class, $result);
        self::assertTrue($result->hasValue());
        self::assertTrue($pattern->hasVariable);
        self::assertSame('42', $result->path['id']);
    }

    #[Test]
    public function it_treats_a_single_host_component_as_a_host_input(): void
    {
        $pattern = (new UrlPatternBuilder())->host('{:foo}.example.com')->build();

        $result = $pattern->extract('http://xn--fi8h.example.com');

        self::assertNotNull($result);
        self::assertSame('🍅', $result->host['foo']);
    }

    #[Test]
    public function it_parses_multiple_components_as_a_uri(): void
    {
        $pattern = (new UrlPatternBuilder())
            ->scheme('https')
            ->host('{:host}.example.com')
            ->path('/users/:id')
            ->build();

        $result = $pattern->extract('https://foo.example.com/users/42');

        self::assertNotNull($result);
        self::assertSame('foo', $result->host['host']);
        self::assertSame('42', $result->path['id']);
    }

    #[Test]
    public function it_rejects_unexpected_uri_components(): void
    {
        $pattern = UrlPatternBuilder::from('/users/:id')->build();

        self::assertTrue($pattern->match('/users/42?foo=bar'));
        self::assertTrue($pattern->match('/users/42#section'));
    }

    #[Test]
    public function it_decodes_path_captures(): void
    {
        $pattern = UrlPatternBuilder::from('/:foo')->build();
        $result = $pattern->extract('/%F0%9F%8D%85');

        self::assertInstanceOf(Result::class, $result);
        self::assertSame('🍅', $result->path['foo']);
    }

    #[Test]
    public function it_extracts_multiple_groups(): void
    {
        $pattern = UrlPattern::from('/users/:user/books/:book');

        $result = $pattern->extract('/users/42/books/123');
        self::assertInstanceOf(Result::class, $result);

        self::assertSame('42', $result->path['user']);
        self::assertSame('123', $result->path['book']);
    }

    #[Test]
    public function it_resolves_a_relative_path_against_the_base_url(): void
    {
        $pattern = UrlPattern::from(
            '/users/:id',
            'https://example.com/api/',
        );

        self::assertTrue($pattern->match('https://example.com/users/42'));
    }

    #[Test]
    #[DataProvider('provideInvalidInput')]
    public function it_throws_an_exception_on_syntax_error(string $input): void
    {
        $this->expectException(SyntaxError::class);

        UrlPattern::from($input);
    }

    public static function provideInvalidInput(): iterable
    {
        yield ['https://example.org/%('];
        yield ['https://example.org/%(('];
        yield ['(\\'];
        yield ['()'];
    }

    #[Test]
    public function it_can_return_implicit_components_value(): void
    {
        $pattern = (new UrlPatternBuilder())
            ->path('/hello/{:name}')
            ->host('{:subdomain.}?localhost')
            ->build();

        $result = $pattern->extract('http://api.localhost:4000/hello/john?search=world');

        self::assertInstanceOf(Result::class, $result);
        self::assertSame(4000, $result->port->integer(0, 80));
        self::assertSame('4000', $result->port->implicit());

        self::assertTrue($result->query->hasValue());
        self::assertSame('search=world', $result->query->implicit());
        self::assertSame('search=world', $result->query->input);

        self::assertFalse($result->fragment->hasValue());
        self::assertNull($result->fragment->implicit());
        self::assertSame('', $result->fragment->input);

        self::assertSame('/hello/john', $result->path->input);
        self::assertNull($result->path->implicit());
        self::assertSame('john', $result->path->string('name'));
    }
}
