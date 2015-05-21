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
            ->withPathExtension('php')
            ->withoutQueryValues(['toto'])
            ->withoutPathSegments([34])
            ->withoutHostLabels([23, 18]);

        $this->assertSame($same, $this->builder);
    }

    public function testWithoutQueryValues()
    {
        $url = $this->builder->withoutQueryValues(['kingkong'])->getUrl();
        $this->assertSame('foo=bar%20baz', $url->getQuery());
    }

    public function testWithoutPathSegments()
    {
        $url = $this->builder->withoutPathSegments([0, 1])->getUrl();
        $this->assertSame('/the/sky.php', $url->getPath());
    }

    public function testWithoutHostLabels()
    {
        $url = $this->builder->withoutHostLabels([0])->getUrl();
        $this->assertSame('example.com', $url->getHost());
    }

    public function testFilterQueryValues()
    {
        $url = $this->builder->filterQueryValues(function ($value) {
            return $value == 'toto';
        })->getUrl();

        $this->assertSame('kingkong=toto', $url->getQuery());
    }

    public function testFilterPathSegments()
    {
        $url = $this->builder->filterPathSegments(function ($value) {
            return strpos($value, 't') === false;
        })->getUrl();

        $this->assertSame('/sky.php', $url->getPath());
    }

    public function testFilterHostLabels()
    {
        $url = $this->builder->filterHostLabels(function ($value) {
            return strpos($value, 'w') === false;
        })->getUrl();

        $this->assertSame('example.com', $url->getHost());
    }

    public function testAppendPath()
    {
        $url = $this->builder->appendPath('/added')->getUrl();
        $this->assertSame('/path/to/the/sky.php/added', (string) $url->getPath());
    }

    public function testAppendHost()
    {
        $url = $this->builder->appendHost('added')->getUrl();
        $this->assertSame('www.example.com.added', (string) $url->getHost());
    }

    public function testPrependPath()
    {
        $url = $this->builder->prependPath('/added')->getUrl();
        $this->assertSame('/added/path/to/the/sky.php', (string) $url->getPath());
    }

    public function testPrependHost()
    {
        $url = $this->builder->prependHost('added')->getUrl();
        $this->assertSame('added.www.example.com', (string) $url->getHost());
    }

    public function testReplacePathSegment()
    {
        $url = $this->builder->replacePathSegment('replace', 3)->getUrl();
        $this->assertSame('/path/to/the/replace', (string) $url->getPath());
    }

    public function testReplaceHostLabel()
    {
        $url = $this->builder->replaceHostLabel('thephpleague', 1)->getUrl();
        $this->assertSame('www.thephpleague.com', (string) $url->getHost());
    }

    public function testWithPathExtension()
    {
        $url = $this->builder->withPathExtension('asp')->getUrl();
        $this->assertSame('/path/to/the/sky.asp', (string) $url->getPath());
    }

    public function testToString()
    {
        $builder = Url\Url::createFromUrl('http://www.example.com/path/to/the/sky.php?kingkong=toto&foo=bar+baz#doc3');
        $this->assertSame($builder->__toString(), $this->builder->__toString());
    }
}
