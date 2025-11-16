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

use League\Uri\Components\Query;
use League\Uri\Exceptions\SyntaxError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_map;
use function parse_url;
use function serialize;
use function unserialize;

#[CoversClass(Urn::class)]
#[CoversClass(UrnComparisonMode::class)]
final class UrnTest extends TestCase
{
    #[DataProvider('validUrns')]
    public function test_it_can_parse_urn(
        string $urn,
        string $nid,
        string $nss,
        ?string $rComponent,
        ?string $qComponent,
        ?string $fComponent,
    ): void {
        $urnObj = Urn::fromString($urn);
        self::assertSame($nid, $urnObj->getNid());
        self::assertSame($nss, $urnObj->getNss());
        self::assertSame($rComponent, $urnObj->getRComponent());
        self::assertSame($qComponent, $urnObj->getQComponent());
        self::assertSame($fComponent, $urnObj->getFComponent());
        self::assertSame($urn, $urnObj->__toString());
    }

    public static function validUrns(): iterable
    {
        yield 'basic URN' => [
           'urn' => 'urn:example:animal:nose',
           'nid' => 'example',
           'nss' => 'animal:nose',
           'rComponent' => null,
           'qComponent' => null,
           'fComponent' => null,
        ];

        yield 'URN - with components' => [
            'urn' => 'urn:example:animal:ferret:nose?+weight=2.3;length=5.1?=profile=standard#section2',
            'nid' => 'example',
            'nss' => 'animal:ferret:nose',
            'rComponent' => 'weight=2.3;length=5.1',
            'qComponent' => 'profile=standard',
            'fComponent' => 'section2',
        ];

        yield 'URN - NSS with dashes' => [
            'urn' => 'urn:uuid:347a4f5f-9a01-4d56-9a45-86cce48e60c9',
            'nid' => 'uuid',
            'nss' => '347a4f5f-9a01-4d56-9a45-86cce48e60c9',
            'rComponent' => null,
            'qComponent' => null,
            'fComponent' => null,
        ];

        yield 'URN - NSS with normalization' => [
            'urn' => 'urn:0123456789aBcDeFgHiJkLmNoPqRsTuV:Example',
            'nid' => '0123456789aBcDeFgHiJkLmNoPqRsTuV',
            'nss' => 'Example',
            'rComponent' => null,
            'qComponent' => null,
            'fComponent' => null,
        ];

        yield 'URN - NSS with slash' => [
            'urn' => 'urn:example:once/twice',
            'nid' => 'example',
            'nss' => 'once/twice',
            'rComponent' => null,
            'qComponent' => null,
            'fComponent' => null,
        ];

        yield 'URN - NSS with reserved character (' => [
            'urn' => 'urn:example:(',
            'nid' => 'example',
            'nss' => '(',
            'rComponent' => null,
            'qComponent' => null,
            'fComponent' => null,
        ];

        yield 'URN - NSS with reserved character )' => [
            'urn' => 'urn:example:)',
            'nid' => 'example',
            'nss' => ')',
            'rComponent' => null,
            'qComponent' => null,
            'fComponent' => null,
        ];

        yield "URN - NSS with reserved character '" => [
            'urn' => "urn:example:'",
            'nid' => 'example',
            'nss' => "'",
            'rComponent' => null,
            'qComponent' => null,
            'fComponent' => null,
        ];

        yield 'URN - NSS with reserved character /' => [
            'urn' => 'urn:example:/',
            'nid' => 'example',
            'nss' => '/',
            'rComponent' => null,
            'qComponent' => null,
            'fComponent' => null,
        ];

        yield 'URN - NSS with reserved character *' => [
            'urn' => 'urn:example:*',
            'nid' => 'example',
            'nss' => '*',
            'rComponent' => null,
            'qComponent' => null,
            'fComponent' => null,
        ];

        yield 'URN - NSS with reserved character +' => [
            'urn' => 'urn:example:+',
            'nid' => 'example',
            'nss' => '+',
            'rComponent' => null,
            'qComponent' => null,
            'fComponent' => null,
        ];

        yield 'URN - NSS with reserved character ,' => [
            'urn' => 'urn:example:,',
            'nid' => 'example',
            'nss' => ',',
            'rComponent' => null,
            'qComponent' => null,
            'fComponent' => null,
        ];

        yield 'URN - NSS with reserved character ;' => [
            'urn' => 'urn:example:;',
            'nid' => 'example',
            'nss' => ';',
            'rComponent' => null,
            'qComponent' => null,
            'fComponent' => null,
        ];

        yield 'URN - NSS with reserved character @' => [
            'urn' => 'urn:example:@',
            'nid' => 'example',
            'nss' => '@',
            'rComponent' => null,
            'qComponent' => null,
            'fComponent' => null,
        ];

        yield 'URN - NSS with reserved character =' => [
            'urn' => 'urn:example:=',
            'nid' => 'example',
            'nss' => '=',
            'rComponent' => null,
            'qComponent' => null,
            'fComponent' => null,
        ];

        yield 'URN - NID with numeric characters:' => [
            'urn' => 'urn:01234:example',
            'nid' => '01234',
            'nss' => 'example',
            'rComponent' => null,
            'qComponent' => null,
            'fComponent' => null,
        ];

        yield 'URN - NID with characters cases:' => [
            'urn' => 'urn:xmpP:example',
            'nid' => 'xmpP',
            'nss' => 'example',
            'rComponent' => null,
            'qComponent' => null,
            'fComponent' => null,
        ];

        yield 'URN - NID with dashes in the middle:' => [
            'urn' => 'urn:0-------------e:Example',
            'nid' => '0-------------e',
            'nss' => 'Example',
            'rComponent' => null,
            'qComponent' => null,
            'fComponent' => null,
        ];

        yield 'URN - QComponent' =>  [
            'urn' => 'urn:example:once/twice/thrice/fource?=ONE=1&TWO=22',
            'nid' => 'example',
            'nss' => 'once/twice/thrice/fource',
            'rComponent' => null,
            'qComponent' => 'ONE=1&TWO=22',
            'fComponent' => null,
        ];

        yield 'URN - QComponent and RComponent' =>  [
            'urn' => 'urn:example:once/twice/thrice/fource?+ONE=1&TWO=22?=apple=11&banana=22',
            'nid' => 'example',
            'nss' => 'once/twice/thrice/fource',
            'rComponent' => 'ONE=1&TWO=22',
            'qComponent' => 'apple=11&banana=22',
            'fComponent' => null,
        ];

        yield 'URN - QComponent and FComponent' =>  [
            'urn' => 'urn:example:once/twice/thrice/fource?=ONE=1&TWO=22#here',
            'nid' => 'example',
            'nss' => 'once/twice/thrice/fource',
            'rComponent' => null,
            'qComponent' => 'ONE=1&TWO=22',
            'fComponent' => 'here',
        ];

        yield 'URN - QComponent with slash' =>  [
            'urn' => 'urn:example:once/twice/thrice/fource?=q/r/',
            'nid' => 'example',
            'nss' => 'once/twice/thrice/fource',
            'rComponent' => null,
            'qComponent' => 'q/r/',
            'fComponent' => null,
        ];
    }

    #[DataProvider('providesInvalidUrn')]
    public function test_it_fails_parsing_invalid_urns(string $urn): void
    {
        self::assertNull(Urn::parse($urn));
    }

    public static function providesInvalidUrn(): iterable
    {
        yield 'invalid urn - missing NID and NSS' => ['urn' => 'urn:'];
        yield 'invalid urn - missing NSS' => ['urn' => 'urn:example:'];
        yield 'invalid urn - missing fcomponent when delimiter is present at the end' => ['urn' => 'urn:example:once/twice/thrice/fource?+'];
        yield 'invalid urn - missing fcomponent when delimiter is present in the middle' => ['urn' => 'urn:example:once/twice/thrice/fource?+?=apple=11&banana=22'];
        yield 'invalid urn - missing fcomponent when delimiter is present before fragment' => ['urn' => 'urn:example:once/twice/thrice/fource?+#here'];
        yield 'invalid urn - missing qcomponent when delimiter is present at the end' => ['urn' => 'urn:example:once/twice/thrice/fource?='];
        yield 'invalid urn - missing qcomponent when delimiter is present in the middle' => ['urn' => 'urn:example:once/twice/thrice/fource?+apple=11&banana=22?='];
        yield 'invalid urn - missing qcomponent when delimiter is present before fragment' => ['urn' => 'urn:example:once/twice/thrice/fource?=#here'];
        yield 'invalid urn - the reserved character ? is not encoded in the NSS part' => ['urn' => 'urn:example:/path?to/it'];
        yield 'invalid urn - the reserved character % is not encoded in the NSS part' => ['urn' => 'urn:example:/path%to/it'];
        yield 'invalid urn - using utf8 codepoint' => ['urn' => 'urn:example:😈'];
        yield 'invalid urn - component are unordered' => ['urn' => 'urn:example:animal:nose?=foo/bar?+toto'];
    }

    public function test_it_can_encode_invalid_characters_on_withers(): void
    {
        $urn = Urn::fromString('urn:example:animal:nose');
        $newUrn = $urn->withNss('😈');

        self::assertSame('urn:example:%F0%9F%98%88', $newUrn->toString());
    }

    public function test_it_can_encode_utf8_characters_and_spaces_on_withers(): void
    {
        $urn = Urn::fromString('urn:example:animal:nose');

        self::assertSame('urn:example:Hello%20world!%20%F0%9F%99%82', $urn->withNss('Hello world! 🙂')->toString());
        self::assertSame('urn:example:animal:nose#Hello%20world!%20%F0%9F%99%82', $urn->withFComponent('Hello world! 🙂')->toString());
        self::assertSame('urn:example:animal:nose?=Hello%20world!%20%F0%9F%99%82', $urn->withQComponent('Hello world! 🙂')->toString());
        self::assertSame('urn:example:animal:nose?+Hello%20world!%20%F0%9F%99%82', $urn->withRComponent('Hello world! 🙂')->toString());
    }

    public function test_it_can_encode_component_delimiters_on_withers(): void
    {
        $urn = Urn::fromString('urn:example:animal:nose');

        self::assertSame('urn:example:animal:nose?=%23Hello%3F+world%3F=', $urn->withQComponent('#Hello?+world?=')->toString());
        self::assertSame('urn:example:animal:nose?+%23Hello%3F+world%3F=', $urn->withRComponent('#Hello?+world?=')->toString());
        self::assertSame('urn:example:animal:nose#%23Hello%3F+world%3F=', $urn->withFComponent('#Hello?+world?=')->toString());
    }

    public function test_it_can_use_uri_components_on_components(): void
    {
        $query = Query::fromPairs([
            ['foo', 'bar'],
            ['fo&o', 'b?ar'],
        ]);
        $urn = Urn::fromString('urn:example:animal:nose')
            ->withRComponent($query)
            ->withQComponent($query)
            ->withFComponent($query);

        self::assertSame('urn:example:animal:nose?+foo=bar&fo%26o=b%3Far?=foo=bar&fo%26o=b%3Far#foo=bar&fo%26o=b%3Far', $urn->toString());
    }

    public function test_it_returns_the_same_instance_if_nothing_changes(): void
    {
        $urn = Urn::fromString('urn:example:animal:nose');
        $newUrn = $urn
            ->withNss('animal:nose')
            ->withNid('example')
            ->withFComponent(null)
            ->normalize();

        self::assertSame($urn, $newUrn);
    }

    #[DataProvider('providesUrnForComparison')]
    public function test_it_can_compare_urns(
        string $first,
        string $second,
        UrnComparisonMode $comparisonMode,
        bool $expected
    ): void {
        self::assertSame($expected, Urn::fromString($first)->equals(Urn::fromString($second), $comparisonMode));
    }

    public static function providesUrnForComparison(): iterable
    {
        yield 'basic comparison' => [
            'first' => 'urn:example:animal:nose',
            'second' => 'urn:example:animal:nose',
            'comparisonMode' => UrnComparisonMode::ExcludeComponents,
            'expected' => true,
        ];

        yield 'basic comparison uses normalization' => [
            'first' => 'urn:example:animal:nose',
            'second' => 'UrN:EXAMple:animal:nose',
            'comparisonMode' => UrnComparisonMode::ExcludeComponents,
            'expected' => true,
        ];

        yield 'basic comparison fails between 2 distincts URN' => [
            'first' => 'urn:example:animal:nose',
            'second' => 'urn:example:vegetable:root',
            'comparisonMode' => UrnComparisonMode::ExcludeComponents,
            'expected' => false,
        ];

        yield 'basic comparison does not take into account the components' => [
            'first' => 'urn:example:animal:nose',
            'second' => 'urn:example:animal:nose?=foo/bar',
            'comparisonMode' => UrnComparisonMode::ExcludeComponents,
            'expected' => true,
        ];

        yield 'comparison can use component if explicitly set' => [
            'first' => 'urn:example:animal:nose',
            'second' => 'urn:example:animal:nose?=foo/bar',
            'comparisonMode' => UrnComparisonMode::IncludeComponents,
            'expected' => false,
        ];
    }

    public function test_it_can_be_serialized(): void
    {
        $urn = Urn::fromRfc2141('example', 'animal:nose')->withQComponent('foo/bar');
        $urnB = unserialize(serialize($urn));

        self::assertInstanceOf(Urn::class, $urnB);
        self::assertSame($urn->toString(), $urnB->toString());
        self::assertTrue($urnB->equals($urn));
        self::assertTrue($urnB->equals($urn, UrnComparisonMode::IncludeComponents));
        self::assertSame([
            'scheme' => 'urn',
            'nid' => 'example',
            'nss' => 'animal:nose',
            'r_component' => null,
            'q_component' => 'foo/bar',
            'f_component' => null,
        ], $urnB->__debugInfo());
        self::assertSame(json_encode($urnB->jsonSerialize()), json_encode($urn));
    }

    public function test_it_can_use_conditional(): void
    {
        $query = Query::fromPairs([
            ['foo', 'bar'],
            ['fo&o', 'b?ar'],
        ]);
        $start = Urn::new('urn:example:animal:nose');
        $urn = $start
            ->when(
                fn (Urn $urn) => null === $urn->getRComponent(),
                fn (Urn $urn) => $urn->withRComponent($query),
                fn (Urn $urn) => $urn->withRComponent(null),
            );

        $urnBis = $urn
            ->when(
                fn (Urn $urn) => null === $urn->getRComponent(),
                fn (Urn $urn) => $urn->withRComponent($query),
                fn (Urn $urn) => $urn->withRComponent(null),
            );

        self::assertSame('urn:example:animal:nose?+foo=bar&fo%26o=b%3Far', $urn->toString());
        self::assertTrue($urnBis->equals($start, UrnComparisonMode::IncludeComponents));
    }

    public function test_it_can_be_converted_into_an_iri(): void
    {
        $urn = Urn::fromString('urn:example:%F0%9F%98%88');

        self::assertSame('urn:example:😈', $urn->toDisplayString());
    }

    #[DataProvider('providesOptionalComponents')]
    public function test_it_can_tell_the_optional_component_states(
        string $urn,
        bool $expectedRComponent,
        bool $expectedQComponent,
        bool $expectedFComponent,
    ): void {
        $urn = Urn::fromString($urn);

        self::assertSame($expectedRComponent, $urn->hasRComponent());
        self::assertSame($expectedQComponent, $urn->hasQComponent());
        self::assertSame($expectedFComponent, $urn->hasFComponent());
        self::assertSame($expectedRComponent || $expectedQComponent || $expectedFComponent, $urn->hasOptionalComponent());
    }

    public static function providesOptionalComponents(): iterable
    {
        yield 'no optional component found' => [
            'urn' => 'urn:example:animal:nose',
            'expectedRComponent' => false,
            'expectedQComponent' => false,
            'expectedFComponent' => false,
        ];

        yield 'r-component found' => [
            'urn' => 'urn:example:animal:nose?+foo=bar',
            'expectedRComponent' => true,
            'expectedQComponent' => false,
            'expectedFComponent' => false,
        ];

        yield 'q-component found' => [
            'urn' => 'urn:example:animal:nose?=foo=bar',
            'expectedRComponent' => false,
            'expectedQComponent' => true,
            'expectedFComponent' => false,
        ];

        yield 'f-component found' => [
            'urn' => 'urn:example:animal:nose#foo=bar',
            'expectedRComponent' => false,
            'expectedQComponent' => false,
            'expectedFComponent' => true,
        ];
    }

    public function test_it_can_be_created_from_uri_components(): void
    {
        $uri = 'urn:example:animal:nose?=foo=bar';

        self::assertTrue(
            Urn::fromComponents(parse_url($uri))->equals(
                Urn::fromString($uri),
                UrnComparisonMode::IncludeComponents
            )
        );
    }

    public function test_it_can_fail_creating_from_uri_components(): void
    {
        $this->expectException(SyntaxError::class);

        Urn::fromComponents(parse_url('http://example.com/foo=bar'));
    }

    public function test_it_can_be_converted_into_an_uri_object(): void
    {
        $urnString = 'urn:example:animal:nose?=foo=bar';
        $urn = Urn::fromComponents(parse_url($urnString));
        $uri = $urn->resolve();

        self::assertInstanceOf(Uri::class, $uri);
        self::assertSame('=foo=bar', $uri->getQuery());
        self::assertSame('example:animal:nose', $uri->getPath());
        self::assertSame('urn', $uri->getScheme());
        self::assertNull($uri->getFragment());
        self::assertTrue($uri->isOpaque());
    }

    public function test_it_can_compare_rfc2141_urn_examples(): void
    {
        /** @var list<Urn> $urns */
        $urns = array_map(Urn::fromString(...), [
            'URN:foo:a123,456',
            'urn:foo:a123,456',
            'urn:FOO:a123,456',
            'urn:foo:A123,456',
            'urn:foo:a123%2C456',
            'URN:FOO:a123%2c456',
        ]);

        self::assertTrue($urns[0]->equals($urns[1]));
        self::assertTrue($urns[0]->equals($urns[2]));
        self::assertFalse($urns[0]->equals($urns[3]));
        self::assertTrue($urns[4]->equals($urns[5]));
    }

    public function test_it_can_resolve_to_uri_using_uri_template(): void
    {
        $urn = Urn::new('urn:isbn:9782266178945');

        $uri = $urn->resolve();
        self::assertInstanceOf(Uri::class, $uri);
        self::assertSame($urn->toString(), $urn->toString());

        $uri = $urn->resolve('https://openlibrary.org/isbn/{nss}');
        self::assertInstanceOf(Uri::class, $uri);
        self::assertSame('https://openlibrary.org/isbn/9782266178945', $uri->toString());
    }
}
