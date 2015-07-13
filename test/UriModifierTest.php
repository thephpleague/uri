<?php

namespace League\Uri\Test;

use League\Uri\Interfaces;
use League\Uri\Schemes\Registry;
use League\Uri\Uri;
use PHPUnit_Framework_TestCase;

/**
 * @group url
 */
class UrlModifierTest extends PHPUnit_Framework_TestCase
{
    private $url;

    public function setUp()
    {
        $this->url = Uri::createFromComponents(
            new Registry(['http' => 80, 'https' => 443]),
            Uri::parse('http://www.example.com/path/to/the/sky.php?kingkong=toto&foo=bar+baz#doc3')
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
            ->withoutQueryValues(['toto'])
            ->withoutSegments([34])
            ->withoutLabels([23, 18]);

        $this->assertSame($same, $this->url);
    }

    public function testWithoutQueryOffsets()
    {
        $url = $this->url->withoutQueryValues(['kingkong']);
        $this->assertSame('foo=bar%20baz', $url->getQuery());
    }

    public function testSortQueryOffsets()
    {
        $url = $this->url->ksortQuery();
        $this->assertSame('foo=bar%20baz&kingkong=toto', $url->getQuery());
    }


    public function testWithoutSegments()
    {
        $url = $this->url->withoutSegments([0, 1]);
        $this->assertSame('/the/sky.php', $url->getPath());
    }

    public function testWithoutEmptySegments()
    {
        $url = Uri::createFromComponents(
            new Registry(['http' => 80, 'https' => 443]),
            Uri::parse('http://www.example.com/path///to/the//sky.php?kingkong=toto&foo=bar+baz#doc3')
        );
        $url = $url->withoutEmptySegments();
        $this->assertSame('/path/to/the/sky.php', $url->getPath());
    }

    public function testWithoutDotSegments()
    {
        $url = Uri::createFromComponents(
            new Registry(['http' => 80, 'https' => 443]),
            Uri::parse('http://www.example.com/path/../to/the/./sky.php?kingkong=toto&foo=bar+baz#doc3')
        );
        $url = $url->normalize();
        $this->assertSame('/to/the/sky.php', $url->getPath());
    }

    public function testWithoutLabels()
    {
        $url = $this->url->withoutLabels([0]);
        $this->assertSame('example.com', $url->getHost());
    }

    public function testWithoutZoneIdentifier()
    {
        $url = Uri::createFromComponents(
            new Registry(['http' => 80, 'https' => 443]),
            Uri::parse('http://[fe80::1234%25eth0-1]/path/../to/the/./sky.php?kingkong=toto&foo=bar+baz#doc3')
        );
        $this->assertSame('[fe80::1234]', $url->withoutZoneIdentifier()->getHost());
    }

    public function testToUnicode()
    {
        $url = Uri::createFromComponents(
            new Registry(['http' => 80, 'https' => 443]),
            Uri::parse('ftp://xn--mgbh0fb.xn--kgbechtv/where/to/go')
        );
        $this->assertSame('مثال.إختبار', $url->toUnicode()->getHost());
    }

    public function testToAscii()
    {
        $url = Uri::createFromComponents(
            new Registry(['http' => 80, 'https' => 443]),
            Uri::parse('ftp://مثال.إختبار/where/to/go')
        );
        $this->assertSame('xn--mgbh0fb.xn--kgbechtv', $url->toAscii()->getHost());
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

    public function testWithTrailingSlash()
    {
        $url = $this->url->withTrailingSlash();
        $this->assertSame('/path/to/the/sky.php/', (string) $url->getPath());
    }

    public function testWithoutTrailingSlash()
    {
        $url =Uri::createFromComponents(
            new Registry(['http' => 80, 'https' => 443]),
            Uri::parse('http://www.example.com/')
        );
        $this->assertSame('', (string) $url->withoutTrailingSlash()->getPath());
    }
}
