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
    private $urlBuilder;

    public function setUp()
    {
        $this->urlBuilder = new Builder(
            'http://www.example.com/path/to/the/sky.php?kingkong=toto&foo=bar+baz#doc3'
        );
    }

    public function testMergeQueryParameters()
    {
        $url = $this->urlBuilder->mergeQueryParameters(['john' => 'doe the john', 'foo' => ''])->getUrl();
        $this->assertSame('kingkong=toto&foo&john=doe%20the%20john', (string) $url->getQuery());
    }

    public function testReturnSameInstance()
    {
        $same = $this->urlBuilder
            ->mergeQueryParameters(['rhoo' => null])
            ->withExtension('php')
            ->withoutQueryParameters(['toto'])
            ->withoutSegments([34])
            ->withoutLabels([23, 18]);

        $this->assertSame($same, $this->urlBuilder);
    }

    public function testWithoutQueryParameters()
    {
        $url = $this->urlBuilder->withoutQueryParameters(['kingkong'])->getUrl();
        $this->assertSame('foo=bar%20baz', $url->getQuery());
    }

    public function testWithoutSegments()
    {
        $url = $this->urlBuilder->withoutSegments([0, 1])->getUrl();
        $this->assertSame('/the/sky.php', $url->getPath());
    }

    public function testWithoutEmptySegments()
    {
        $urlBuilder = new Builder(
            'http://www.example.com/path///to/the//sky.php?kingkong=toto&foo=bar+baz#doc3'
        );
        $url = $urlBuilder->withoutEmptySegments()->getUrl();
        $this->assertSame('/path/to/the/sky.php', $url->getPath());
    }

    public function testWithoutLabels()
    {
        $url = $this->urlBuilder->withoutLabels([0])->getUrl();
        $this->assertSame('example.com', $url->getHost());
    }

    public function testFilterQueryValues()
    {
        $url = $this->urlBuilder->filterQueryValues(function ($value) {
            return $value == 'toto';
        })->getUrl();

        $this->assertSame('kingkong=toto', $url->getQuery());
    }

    public function testFilterSegments()
    {
        $url = $this->urlBuilder->filterSegments(function ($value) {
            return strpos($value, 't') === false;
        })->getUrl();

        $this->assertSame('/sky.php', $url->getPath());
    }

    public function testFilterLabels()
    {
        $url = $this->urlBuilder->filterLabels(function ($value) {
            return strpos($value, 'w') === false;
        })->getUrl();

        $this->assertSame('example.com', $url->getHost());
    }

    public function testAppendSegments()
    {
        $url = $this->urlBuilder->appendSegments('/added')->getUrl();
        $this->assertSame('/path/to/the/sky.php/added', (string) $url->getPath());
    }

    public function testAppendLabels()
    {
        $url = $this->urlBuilder->appendLabels('added')->getUrl();
        $this->assertSame('www.example.com.added', (string) $url->getHost());
    }

    public function testPrependSegments()
    {
        $url = $this->urlBuilder->prependSegments('/added')->getUrl();
        $this->assertSame('/added/path/to/the/sky.php', (string) $url->getPath());
    }

    public function testPrependLabels()
    {
        $url = $this->urlBuilder->prependLabels('added')->getUrl();
        $this->assertSame('added.www.example.com', (string) $url->getHost());
    }

    public function testReplaceSegment()
    {
        $url = $this->urlBuilder->replaceSegment(3, 'replace')->getUrl();
        $this->assertSame('/path/to/the/replace', (string) $url->getPath());
    }

    public function testReplaceLabel()
    {
        $url = $this->urlBuilder->replaceLabel(1, 'thephpleague')->getUrl();
        $this->assertSame('www.thephpleague.com', (string) $url->getHost());
    }

    public function testWithExtension()
    {
        $url = $this->urlBuilder->withExtension('asp')->getUrl();
        $this->assertSame('/path/to/the/sky.asp', (string) $url->getPath());
    }
}
