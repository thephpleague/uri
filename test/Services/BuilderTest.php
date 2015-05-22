<?php

namespace League\Url\Test\Output;

use League\Url\Services\Builder;
use League\Url;
use PHPUnit_Framework_TestCase;

/**
 * @group url
 */
class BuilderTest extends PHPUnit_Framework_TestCase
{
    private $builder;

    public function setUp()
    {
        $this->builder = new Builder(
            'http://www.example.com/path/to/the/sky.php?kingkong=toto&foo=bar+baz#doc3'
        );
    }

    public function testMergeQueryValues()
    {
        $url = $this->builder->mergeQueryValues(['john' => 'doe the john', 'foo' => ''])->getUrl();
        $this->assertSame('kingkong=toto&foo&john=doe%20the%20john', (string) $url->getQuery());
    }

    public function testReturnSameInstance()
    {
        $same = $this->builder
            ->mergeQueryValues(['rhoo' => null])
            ->withExtension('php')
            ->withoutQueryValues(['toto'])
            ->withoutSegments([34])
            ->withoutLabels([23, 18]);

        $this->assertSame($same, $this->builder);
    }

    public function testWithoutQueryValues()
    {
        $url = $this->builder->withoutQueryValues(['kingkong'])->getUrl();
        $this->assertSame('foo=bar%20baz', $url->getQuery());
    }

    public function testWithoutSegments()
    {
        $url = $this->builder->withoutSegments([0, 1])->getUrl();
        $this->assertSame('/the/sky.php', $url->getPath());
    }

    public function testWithoutLabels()
    {
        $url = $this->builder->withoutLabels([0])->getUrl();
        $this->assertSame('example.com', $url->getHost());
    }

    public function testFilterQueryValues()
    {
        $url = $this->builder->filterQueryValues(function ($value) {
            return $value == 'toto';
        })->getUrl();

        $this->assertSame('kingkong=toto', $url->getQuery());
    }

    public function testFilterSegments()
    {
        $url = $this->builder->filterSegments(function ($value) {
            return strpos($value, 't') === false;
        })->getUrl();

        $this->assertSame('/sky.php', $url->getPath());
    }

    public function testFilterLabels()
    {
        $url = $this->builder->filterLabels(function ($value) {
            return strpos($value, 'w') === false;
        })->getUrl();

        $this->assertSame('example.com', $url->getHost());
    }

    public function testAppendSegments()
    {
        $url = $this->builder->appendSegments('/added')->getUrl();
        $this->assertSame('/path/to/the/sky.php/added', (string) $url->getPath());
    }

    public function testAppendLabels()
    {
        $url = $this->builder->appendLabels('added')->getUrl();
        $this->assertSame('www.example.com.added', (string) $url->getHost());
    }

    public function testPrependSegments()
    {
        $url = $this->builder->prependSegments('/added')->getUrl();
        $this->assertSame('/added/path/to/the/sky.php', (string) $url->getPath());
    }

    public function testPrependLabels()
    {
        $url = $this->builder->prependLabels('added')->getUrl();
        $this->assertSame('added.www.example.com', (string) $url->getHost());
    }

    public function testReplaceSegment()
    {
        $url = $this->builder->replaceSegment('replace', 3)->getUrl();
        $this->assertSame('/path/to/the/replace', (string) $url->getPath());
    }

    public function testReplaceLabel()
    {
        $url = $this->builder->replaceLabel('thephpleague', 1)->getUrl();
        $this->assertSame('www.thephpleague.com', (string) $url->getHost());
    }

    public function testWithExtension()
    {
        $url = $this->builder->withExtension('asp')->getUrl();
        $this->assertSame('/path/to/the/sky.asp', (string) $url->getPath());
    }
}
