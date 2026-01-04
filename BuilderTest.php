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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Builder::class)]
final class BuilderTest extends TestCase
{
    public function test_it_can_build_a_new_uri_instance(): void
    {
        $builder = new Builder();
        $changedBuilder = $builder
            ->scheme('https')
            ->userInfo('user', 'pass')
            ->host('wiki.php.net')
            ->port(8080)
            ->path('rf:c/uri_followup')
            ->query('a=1&b=2')
            ->fragment('uri_building');

        self::assertSame($changedBuilder, $builder);
        self::assertSame('https://user:pass@wiki.php.net:8080/rf:c/uri_followup?a=1&b=2#uri_building', $builder->build()->toAsciiString());
    }

    public function test_it_can_build_a_new_uri_instance_using_the_constructor(): void
    {
        $builder = new Builder(
            scheme: 'HtTpS',
            username: 'user',
            password: 'pass',
            host: 'WiKi.PhP.NeT',
            path: 'rf:c/uri_followup',
            query: 'a=1&b=2',
            fragment: 'uri_building'
        );

        self::assertSame('https://user:pass@wiki.php.net/rf:c/uri_followup?a=1&b=2#uri_building', $builder->build()->toAsciiString());
    }

    public function test_it_fails_to_build_a_new_uri_if_the_user_info_is_present_and_the_host_is_missing(): void
    {
        $this->expectException(SyntaxError::class);

        (new Builder())
            ->scheme('https')
            ->userInfo('user', 'pass')
            ->path('rf:c/uri_followup')
            ->query('a=1&b=2')
            ->fragment('uri_building')
            ->build();
    }

    public function test_it_fails_to_build_a_new_uri_if_the_port_is_present_and_the_host_is_missing(): void
    {
        $this->expectException(SyntaxError::class);

        (new Builder())
            ->scheme('https')
            ->port(8080)
            ->path('rf:c/uri_followup')
            ->query('a=1&b=2')
            ->fragment('uri_building')
            ->build();
    }

    public function test_it_fails_to_build_a_new_uri_if_the_scheme_is_missing_and_the_path_contains_colone_before_a_slash(): void
    {
        $this->expectException(SyntaxError::class);

        (new Builder(
            path: 'rf:c',
            query: 'a=1&b=2',
            fragment: 'uri_building'
        ))->build();
    }

    public function test_it_fails_if_the_scheme_contains_invalid_characters(): void
    {
        $this->expectException(SyntaxError::class);

        (new Builder())->scheme('htt*s')->build();
    }

    public function test_it_encodes_path_contains_invalid_characters(): void
    {
        self::assertSame(
            'rfc/uri_f%C3%B2llowup',
            (new Builder(path: 'rfc/uri_fòllowup'))->build()->getPath()
        );
    }

    public function test_building_without_calling_any_setter(): void
    {
        self::assertSame('', (new Builder())->build()->toString());
    }

    public function test_building_with_a_base_uri(): void
    {
        self::assertSame('https://example.com', (new Builder())->build('https://example.com')->toString());
    }

    public function test_it_can_resolve_the_uri_against_a_base_uri(): void
    {
        $builder = (new Builder())
            ->userInfo('user', 'pass')
            ->host('host')
            ->path('./.././toto')
            ->scheme('https');

        self::assertSame('https://user:pass@host/./.././toto', $builder->build()->toAsciiString());
        self::assertSame('https://user:pass@host/toto', $builder->build('https://host/toto')->toAsciiString());
    }

    public function test_it_can_be_reset(): void
    {
        $builder = (new Builder())
            ->userInfo('user', 'pass')
            ->host('host')
            ->path('./.././toto')
            ->scheme('https');

        self::assertSame('https://user:pass@host/./.././toto', $builder->build()->toAsciiString());
        self::assertSame('', $builder->reset()->build()->toString());
    }

    public function test_tap_calls_callback_with_self_and_returns_same_instance(): void
    {
        $builder = new Builder();

        $called = false;
        $callback = function (Builder $b) use (&$called, $builder) {
            $called = true;
            self::assertSame($builder, $b);
        };

        $result = $builder->tap($callback);

        self::assertTrue($called);
        self::assertSame($builder, $result);
    }

    public function test_tap_allows_builder_mutation(): void
    {
        $builder = (new Builder())->scheme('http');
        $builder->tap(function (Builder $b) {
            $b->host('example.com');
        });

        $uri = $builder->build();

        self::assertSame('example.com', $uri->getHost());
        self::assertSame('http', $uri->getScheme());
    }

    public function test_authority_with_host_only(): void
    {
        $uri = (new Builder())->authority('example.com')->build();

        self::assertSame('example.com', $uri->getHost());
        self::assertNull($uri->getPort());
    }

    public function test_authority_with_host_and_port(): void
    {
        $uri = (new Builder())->authority('example.com:8080')->build();

        self::assertSame('example.com:8080', $uri->getAuthority());
        self::assertSame('example.com', $uri->getHost());
        self::assertSame(8080, $uri->getPort());
    }

    public function test_authority_with_host_port_and_user_info(): void
    {
        $uri = (new Builder())->authority('john:secret@example.com:8080')->build();

        self::assertSame('john:secret@example.com:8080', $uri->getAuthority());
        self::assertSame('john', $uri->getUsername());
        self::assertSame('secret', $uri->getPassword());
        self::assertSame('example.com', $uri->getHost());
        self::assertSame(8080, $uri->getPort());
    }

    public function test_unsetting_authority_clear_host_and_port(): void
    {
        $uri = (new Builder())
            ->host('example.com')
            ->port(8080)
            ->authority(null)
            ->build();

        self::assertNotSame('example.com', $uri->getHost());
        self::assertNotSame(8080, $uri->getPort());
    }

    public function test_authority_can_be_overridden(): void
    {
        $uri = (new Builder())
            ->authority('example.com:8080')
            ->authority('api.example.com:443')
            ->build();

        self::assertSame('api.example.com:443', $uri->getAuthority());
        self::assertNotSame('example.com:8080', (string)$uri);
    }

    public function test_guard_with_valid_builder_returns_same_instance(): void
    {
        $builder = (new Builder())
            ->scheme('https')
            ->host('example.com');

        self::assertSame($builder, $builder->guard());
    }

    public function test_guard_with_invalid_builder_throws_exception(): void
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('The current builder cannot generate a valid URI.');

        (new Builder())->scheme('https')->guard();
    }
}
