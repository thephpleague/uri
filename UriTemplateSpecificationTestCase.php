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

use JsonException;
use League\Uri\UriTemplate\Template;
use League\Uri\UriTemplate\VariableBag;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;
use const JSON_THROW_ON_ERROR;

abstract class UriTemplateSpecificationTestCase extends TestCase
{
    protected static string $rootPath = __DIR__.'/../vendor/uri-templates/uritemplate-test';

    /** @var array<string> */
    protected static array $testFilenames;

    /**
     * @test
     * @dataProvider uriTemplateSpecificationDataProvider
     */
    public function it_can_pass_http_tests(string $title, int|null $level, array $variables, string $input, string|array|false $expected): void
    {
        if (false === $expected) {
            $this->expectException(Throwable::class);
        }

        $result = Template::createFromString($input)->expand(new VariableBag($variables));

        if (is_array($expected)) {
            self::assertContains($result, $expected);
        } else {
            self::assertSame($expected, $result);
        }
    }

    /**
     * @throws JsonException
     * @return iterable<string, array{
     *     title:string,
     *     level:int|null,
     *     variables:array{string, string|int},
     *     input:string,
     *     expected:string|array<string>|false
     * }>
     */
    public static function uriTemplateSpecificationDataProvider(): iterable
    {
        foreach (static::$testFilenames as $path) {
            $path = static::$rootPath.'/'.ltrim($path, '/');
            if (false === $content = file_get_contents($path)) {
                throw new RuntimeException("unable to connect to the path `$path`.");
            }

            /** @var array $records */
            $records = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            foreach ($records as $title => $testSuite) {
                $level = $testSuite['level'] ?? null;
                $variables = $testSuite['variables'];
                foreach ($testSuite['testcases'] as $offset => [$input, $expected]) {
                    yield $title.' - '.$level.' # '.($offset + 1) => [
                        'title' => $title,
                        'level' => $level,
                        'variables' => $variables,
                        'input' => $input,
                        'expected' => $expected,
                    ];
                }
            }
        }
    }
}
