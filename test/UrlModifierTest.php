<?php

namespace League\Uri\Test;

use League\Uri\Interfaces;
use League\Uri\Url;
use PHPUnit_Framework_TestCase;

/**
 * @group url
 */
class UrlModifierTest extends PHPUnit_Framework_TestCase
{
    private $url;

    public function setUp()
    {
        $this->url = Url::createFromString(
            'http://www.example.com/path/to/the/sky.php?kingkong=toto&foo=bar+baz#doc3'
        );
    }

    public function testMergeQueryParameters()
    {
        $url = $this->url->mergeQuery(['john' => 'doe the john', 'foo' => null]);
        $this->assertSame('kingkong=toto&foo&john=doe%20the%20john', (string) $url->getQuery());
    }

    public function testReturnSameInstance()
    {
        $same = $this->url
            ->mergeQuery(['kingkong' => 'toto'])
            ->withExtension('php')
            ->withoutQueryOffsets(['toto'])
            ->withoutSegments([34])
            ->withoutLabels([23, 18]);

        $this->assertSame($same, $this->url);
    }

    public function testWithoutQueryOffsets()
    {
        $url = $this->url->withoutQueryOffsets(['kingkong']);
        $this->assertSame('foo=bar%20baz', $url->getQuery());
    }

    public function testSortQueryOffsets()
    {
        $url = $this->url->sortQueryOffsets();
        $this->assertSame('foo=bar%20baz&kingkong=toto', $url->getQuery());
    }


    public function testWithoutSegments()
    {
        $url = $this->url->withoutSegments([0, 1]);
        $this->assertSame('/the/sky.php', $url->getPath());
    }

    public function testWithoutEmptySegments()
    {
        $url = Url::createFromString(
            'http://www.example.com/path///to/the//sky.php?kingkong=toto&foo=bar+baz#doc3'
        );
        $url = $url->withoutEmptySegments();
        $this->assertSame('/path/to/the/sky.php', $url->getPath());
    }

    public function testWithoutDotSegments()
    {
        $url = Url::createFromString(
            'http://www.example.com/path/../to/the/./sky.php?kingkong=toto&foo=bar+baz#doc3'
        );
        $url = $url->withoutDotSegments();
        $this->assertSame('/to/the/sky.php', $url->getPath());
    }

    public function testWithoutLabels()
    {
        $url = $this->url->withoutLabels([0]);
        $this->assertSame('example.com', $url->getHost());
    }


    public function testWithoutZoneIdentifier()
    {
        $url = Url::createFromString(
            'http://[fe80::1234%25eth0-1]/path/../to/the/./sky.php?kingkong=toto&foo=bar+baz#doc3'
        );
        $url = $url->withoutZoneIdentifier();
        $this->assertSame('[fe80::1234]', $url->getHost());
    }


    public function testFilterQueryParameters()
    {
        $url = $this->url->filterQuery(function ($value) {
            return $value == 'kingkong';
        }, Interfaces\Collection::FILTER_USE_KEY);

        $this->assertSame('kingkong=toto', $url->getQuery());
    }

    public function testFilterQueryValues()
    {
        $url = $this->url->filterQuery(function ($value) {
            return $value == 'toto';
        }, Interfaces\Collection::FILTER_USE_VALUE);

        $this->assertSame('kingkong=toto', $url->getQuery());
    }

    public function testFilterSegments()
    {
        $url = $this->url->filterPath(function ($value) {
            return strpos($value, 't') === false;
        });

        $this->assertSame('/sky.php', $url->getPath());
    }

    public function testFilterLabels()
    {
        $url = $this->url->filterHost(function ($value) {
            return strpos($value, 'w') === false;
        });

        $this->assertSame('example.com', $url->getHost());
    }

    public function testAppendSegments()
    {
        $url = $this->url->appendPath('/added');
        $this->assertSame('/path/to/the/sky.php/added', (string) $url->getPath());
    }

    public function testAppendLabels()
    {
        $url = $this->url->appendHost('added');
        $this->assertSame('www.example.com.added', (string) $url->getHost());
    }

    public function testPrependSegments()
    {
        $url = $this->url->prependPath('/added');
        $this->assertSame('/added/path/to/the/sky.php', (string) $url->getPath());
    }

    public function testPrependLabels()
    {
        $url = $this->url->prependHost('added');
        $this->assertSame('added.www.example.com', (string) $url->getHost());
    }

    public function testReplaceSegment()
    {
        $url = $this->url->replaceSegment(3, 'replace');
        $this->assertSame('/path/to/the/replace', (string) $url->getPath());
    }

    public function testReplaceLabel()
    {
        $url = $this->url->replaceLabel(1, 'thephpleague');
        $this->assertSame('www.thephpleague.com', (string) $url->getHost());
    }

    public function testWithExtension()
    {
        $url = $this->url->withExtension('asp');
        $this->assertSame('/path/to/the/sky.asp', (string) $url->getPath());
    }
}
