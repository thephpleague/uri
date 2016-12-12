<?php

namespace League\Uri\Test;

use InvalidArgumentException;
use League\Uri;
use League\Uri\Components\Scheme;
use League\Uri\Formatter;
use League\Uri\Schemes\Data as DataUri;
use League\Uri\Schemes\Http as HttpUri;
use PHPUnit_Framework_TestCase;
use Zend\Diactoros\Uri as DiactorosUri;

/**
 * @group formatter
 */
class FormatterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var HttpUri
     */
    private $uri;

    /**
     * @var Formatter
     */
    private $formatter;

    protected function setUp()
    {
        $this->uri = new DiactorosUri(
            'http://login:pass@gwóźdź.pl:443/test/query.php?kingkong=toto&foo=bar+baz#doc3'
        );
        $this->formatter = new Formatter();
    }

    public function testFormatHostAscii()
    {
        $this->assertSame(Formatter::HOST_AS_UNICODE, $this->formatter->getHostEncoding());
        $this->formatter->setHostEncoding(Formatter::HOST_AS_ASCII);
        $this->assertSame(Formatter::HOST_AS_ASCII, $this->formatter->getHostEncoding());
        $uri = HttpUri::createFromString($this->uri->__toString());
        $this->assertSame('xn--gwd-hna98db.pl', $this->formatter->__invoke($uri->host));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidHostEncoding()
    {
        $this->formatter->setHostEncoding('toto');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidQueryEncoding()
    {
        $this->formatter->setQueryEncoding('toto');
    }

    public function testFormatWithSimpleString()
    {
        $expected = 'http://login:pass@xn--gwd-hna98db.pl:443/test/query.php?kingkong=toto&amp;foo=bar+baz#doc3';
        $this->formatter->setQuerySeparator('&amp;');
        $this->formatter->setHostEncoding(Formatter::HOST_AS_ASCII);
        $this->assertSame($expected, $this->formatter->__invoke($this->uri));
    }

    public function testFormatWithZeroes()
    {
        $expected = 'https://example.com/image.jpeg?0#0';
        $uri = new DiactorosUri('https://example.com/image.jpeg?0#0');
        $this->assertSame($expected, $this->formatter->__invoke($uri));
    }

    public function testFormatComponent()
    {
        $scheme = new Scheme('ftp');
        $this->assertSame($scheme->__toString(), $this->formatter->format($scheme));
    }

    public function testFormatHostUnicode()
    {
        $this->formatter->setHostEncoding(Formatter::HOST_AS_UNICODE);
        $uri = HttpUri::createFromString($this->uri->__toString());
        $this->assertSame('gwóźdź.pl', $this->formatter->__invoke($uri->host));
    }

    public function testFormatQueryRFC1738()
    {
        $this->assertSame(PHP_QUERY_RFC3986, $this->formatter->getQueryEncoding());
        $this->formatter->setQueryEncoding(PHP_QUERY_RFC1738);
        $this->assertSame(PHP_QUERY_RFC1738, $this->formatter->getQueryEncoding());
        $uri = HttpUri::createFromString($this->uri->__toString());
        $this->assertSame('kingkong=toto&foo=bar%2Bbaz', $this->formatter->__invoke($uri->query));
    }

    public function testFormatQueryRFC3986()
    {
        $this->formatter->setQueryEncoding(PHP_QUERY_RFC3986);
        $uri = HttpUri::createFromString($this->uri->__toString());
        $this->assertSame('kingkong=toto&foo=bar+baz', $this->formatter->__invoke($uri->query));
    }

    public function testFormatQueryWithSeparator()
    {
        $this->assertSame('&', $this->formatter->getQuerySeparator());
        $this->formatter->setQuerySeparator('&amp;');
        $this->assertSame('&amp;', $this->formatter->getQuerySeparator());
        $uri = HttpUri::createFromString($this->uri->__toString());
        $this->assertSame('kingkong=toto&amp;foo=bar+baz', $this->formatter->__invoke($uri->query));
    }

    public function testFormat()
    {
        $this->formatter->setQuerySeparator('&amp;');
        $this->formatter->setHostEncoding(Formatter::HOST_AS_ASCII);
        $expected = 'http://login:pass@xn--gwd-hna98db.pl:443/test/query.php?kingkong=toto&amp;foo=bar+baz#doc3';
        $this->assertSame($expected, $this->formatter->__invoke($this->uri));
    }

    public function testFormatOpaqueUri()
    {
        $uri = DataUri::createFromString('data:,');
        $this->formatter->setQuerySeparator('&amp;');
        $this->formatter->setHostEncoding(Formatter::HOST_AS_ASCII);
        $this->assertSame($uri->__toString(), $this->formatter->__invoke($uri));
    }


    public function testFormatWithoutAuthority()
    {
        $expected = '/test/query.php?kingkong=toto&amp;foo=bar+baz#doc3';
        $uri = $this->uri->withScheme('')->withPort(null)->withUserInfo('')->withHost('');
        $this->formatter->setQuerySeparator('&amp;');
        $this->formatter->setHostEncoding(Formatter::HOST_AS_ASCII);
        $this->assertSame($expected, $this->formatter->__invoke($uri));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFormatterFailed()
    {
        $this->formatter->__invoke('http://www.example.com');
    }

    public function testFormatterPreservedQuery()
    {
        $expected = 'http://example.com';
        $uri = new DiactorosUri($expected);
        $this->formatter->preserveQuery(true);
        $this->assertSame($expected, (string) $uri);
        $this->assertSame('http://example.com?', $this->formatter->__invoke($uri));
    }

    public function testFormatterPreservedFragment()
    {
        $expected = 'http://example.com';
        $uri = new DiactorosUri($expected);
        $this->formatter->preserveFragment(true);
        $this->assertSame($expected, (string) $uri);
        $this->assertSame('http://example.com#', $this->formatter->__invoke($uri));
    }

    public function testValidFormatterRespectRFC3986()
    {
        $psr7uri = (new DiactorosUri('http://bébé.be'))->withPath('foo/bar');
        $this->assertSame('http://bébé.be/foo/bar', (string) $this->formatter->__invoke($psr7uri));
    }
}
