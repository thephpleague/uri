<?php

namespace League\Uri\Test\Schemes\Generic;

use League\Uri\Interfaces;
use League\Uri\Schemes\Http as HttpUri;
use PHPUnit_Framework_TestCase;

/**
 * @group uri
 */
class HierarchicalUriModifierTest extends PHPUnit_Framework_TestCase
{
    private $uri;

    public function setUp()
    {
        $this->uri = HttpUri::createFromString(
            'http://www.example.com/path/to/the/sky.php?kingkong=toto&foo=bar+baz#doc3'
        );
    }

    public function testMergeQueryParameters()
    {
        $uri = $this->uri->mergeQuery('john=doe%20the%20john&foo');
        $this->assertSame('kingkong=toto&foo&john=doe%20the%20john', (string) $uri->getQuery());
    }

    public function testReturnSameInstance()
    {
        $same = $this->uri
            ->mergeQuery('kingkong=toto')
            ->withExtension('php')
            ->withoutQueryValues(['toto'])
            ->withoutSegments([34])
            ->withoutLabels([23, 18]);

        $this->assertSame($same, $this->uri);
    }

    public function testWithoutQueryOffsets()
    {
        $uri = $this->uri->withoutQueryValues(['kingkong']);
        $this->assertSame('foo=bar%20baz', $uri->getQuery());
    }

    public function testSortQueryOffsets()
    {
        $uri = $this->uri->ksortQuery();
        $this->assertSame('foo=bar%20baz&kingkong=toto', $uri->getQuery());
    }

    public function testWithoutSegments()
    {
        $uri = $this->uri->withoutSegments([0, 1]);
        $this->assertSame('/the/sky.php', $uri->getPath());
    }

    public function testWithoutEmptySegments()
    {
        $uri = HttpUri::createFromString(
            'http://www.example.com/path///to/the//sky.php?kingkong=toto&foo=bar+baz#doc3'
        );
        $uri = $uri->withoutEmptySegments();
        $this->assertSame('/path/to/the/sky.php', $uri->getPath());
    }

    public function testWithoutDotSegments()
    {
        $uri = HttpUri::createFromString(
            'http://www.example.com/path/../to/the/./sky.php?kingkong=toto&foo=bar+baz#doc3'
        );
        $uri = $uri->withoutDotSegments();
        $this->assertSame('/to/the/sky.php', $uri->getPath());
    }

    public function testWithoutLabels()
    {
        $uri = $this->uri->withoutLabels([2]);
        $this->assertSame('example.com', $uri->getHost());
    }

    public function testWithoutZoneIdentifier()
    {
        $uri = HttpUri::createFromString(
            'http://[fe80::1234%25eth0-1]/path/../to/the/./sky.php?kingkong=toto&foo=bar+baz#doc3'
        );
        $this->assertSame('[fe80::1234]', $uri->withoutZoneIdentifier()->getHost());
    }

    public function testToUnicode()
    {
        $uri = HttpUri::createFromString('http://xn--mgbh0fb.xn--kgbechtv/where/to/go');
        $this->assertSame('مثال.إختبار', $uri->hostToUnicode()->getHost());
    }

    public function testToAscii()
    {
        $uri = HttpUri::createFromString('http://مثال.إختبار/where/to/go');
        $this->assertSame('xn--mgbh0fb.xn--kgbechtv', $uri->HostToAscii()->getHost());
    }

    public function testFilterQueryParameters()
    {
        $uri = $this->uri->filterQuery(function ($value) {
            return $value == 'kingkong';
        }, Interfaces\Components\Collection::FILTER_USE_KEY);

        $this->assertSame('kingkong=toto', $uri->getQuery());
    }

    public function testFilterQueryValues()
    {
        $uri = $this->uri->filterQuery(function ($value) {
            return $value == 'toto';
        }, Interfaces\Components\Collection::FILTER_USE_VALUE);

        $this->assertSame('kingkong=toto', $uri->getQuery());
    }

    public function testFilterSegments()
    {
        $uri = $this->uri->filterPath(function ($value) {
            return strpos($value, 't') === false;
        });

        $this->assertSame('/sky.php', $uri->getPath());
    }

    public function testFilterLabels()
    {
        $uri = $this->uri->filterHost(function ($value) {
            return strpos($value, 'w') === false;
        });

        $this->assertSame('example.com', $uri->getHost());
    }

    public function testAppendSegments()
    {
        $uri = $this->uri->appendPath('/added');
        $this->assertSame('/path/to/the/sky.php/added', (string) $uri->getPath());
    }

    public function testAppendLabels()
    {
        $uri = $this->uri->appendHost('added');
        $this->assertSame('www.example.com.added', (string) $uri->getHost());
    }

    public function testPrependSegments()
    {
        $uri = $this->uri->prependPath('/added');
        $this->assertSame('/added/path/to/the/sky.php', (string) $uri->getPath());
    }

    public function testPrependLabels()
    {
        $uri = $this->uri->prependHost('added');
        $this->assertSame('added.www.example.com', (string) $uri->getHost());
    }

    public function testReplaceSegment()
    {
        $uri = $this->uri->replaceSegment(3, 'replace');
        $this->assertSame('/path/to/the/replace', (string) $uri->getPath());
    }

    public function testReplaceLabel()
    {
        $uri = $this->uri->replaceLabel(1, 'thephpleague');
        $this->assertSame('www.thephpleague.com', (string) $uri->getHost());
    }

    public function testWithExtension()
    {
        $uri = $this->uri->withExtension('asp');
        $this->assertSame('/path/to/the/sky.asp', (string) $uri->getPath());
    }

    public function testWithTrailingSlash()
    {
        $uri = $this->uri->withTrailingSlash();
        $this->assertSame('/path/to/the/sky.php/', (string) $uri->getPath());
    }

    public function testWithoutTrailingSlash()
    {
        $uri = HttpUri::createFromString('http://www.example.com/');
        $this->assertSame('', (string) $uri->withoutTrailingSlash()->getPath());
    }
}
