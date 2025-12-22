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
}
