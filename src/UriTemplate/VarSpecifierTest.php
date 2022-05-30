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

use League\Uri\Exceptions\SyntaxError;
use PHPUnit\Framework\TestCase;
use function var_export;

/**
 * @coversDefaultClass \League\Uri\UriTemplate\VarSpecifier
 */
final class VarSpecifierTest extends TestCase
{
    /**
     * @covers ::createFromString
     * @covers ::toString
     * @covers ::__construct
     *
     * @dataProvider providesValidNotation
     */
    public function testItCanBeInstantiatedWithAValidNotation(string $notation): void
    {
        self::assertSame($notation, VarSpecifier::createFromString($notation)->toString());
    }

    public function providesValidNotation(): iterable
    {
        return [
            'simple' => ['notation' => 'var'],
            'variable with underscore' => ['notation' => 'under_score'],
            'variable with digit' => ['notation' => 'd1g1t'],
            'variable with encoded' => ['notation' => 'semi%3Bcolon'],
            'variable with point' => ['notation' => 'p.int'],
            'with explode modifier' => ['notation' => 'var*'],
            'with position modifier' => ['notation' => 'var:5'],
            'complex variable' => ['notation' => 'und.er_sc0re%3B:5'],
        ];
    }

    /**
     * @covers ::createFromString
     *
     * @dataProvider providesInvalidNotation
     */
    public function testItFailsToInstantiatedWithAnInvalidNotationString(string $notation): void
    {
        self::expectException(SyntaxError::class);

        VarSpecifier::createFromString($notation);
    }

    public function providesInvalidNotation(): iterable
    {
        return [
            'using the explode modifier with the position notation' => ['notation' => 'var:*'],
            'with a position modifier ouf of bound' => ['notation' => 'var:10000'],
            'empty string' => ['notation' => ''],
            'unsupported character' => ['notation' => 'bébé'],
        ];
    }

    /**
     * @covers ::position
     * @covers ::modifier
     * @covers ::name
     */
    public function testItCanReturnsTheVarSpecifierProperties(): void
    {
        $varSpecifier = VarSpecifier::createFromString('und.er_sc0re%3B:5');

        self::assertSame('und.er_sc0re%3B', $varSpecifier->name);
        self::assertSame(':', $varSpecifier->modifier);
        self::assertSame(5, $varSpecifier->position);

        $varSpecifier = VarSpecifier::createFromString('und.er_sc0re%3B*');

        self::assertSame('und.er_sc0re%3B', $varSpecifier->name);
        self::assertSame('*', $varSpecifier->modifier);
        self::assertSame(0, $varSpecifier->position);
    }

    /**
     * @covers ::__set_state
     */
    public function testSetState(): void
    {
        $varSpecifier = VarSpecifier::createFromString('und.er_sc0re%3B*');

        self::assertEquals($varSpecifier, eval('return '.var_export($varSpecifier, true).';'));
    }
}
