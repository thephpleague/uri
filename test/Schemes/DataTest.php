<?php

namespace League\Uri\Test\Schemes;

use InvalidArgumentException;
use League\Uri;
use League\Uri\Interfaces;
use League\Uri\Schemes\Data as DataUri;
use PHPUnit_Framework_TestCase;
use RuntimeException;

/**
 * @group uri
 * @group data
 */
class DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validStringUri
     *
     * @param string $str
     * @param string $mimetype
     * @param string $parameters
     * @param string $mediatype
     * @param string $data
     * @param bool   $isBinaryData
     */
    public function testCreateFromString($str, $mimetype, $parameters, $mediatype, $data, $isBinaryData)
    {
        $uri = DataUri::createFromString($str);
        $this->assertSame('data', $uri->getScheme());
        $this->assertSame($mimetype, $uri->path->getMimeType());
        $this->assertSame($parameters, $uri->path->getParameters());
        $this->assertSame($mediatype, $uri->path->getMediaType());
        $this->assertSame($data, $uri->path->getData());
        $this->assertSame($isBinaryData, $uri->path->isBinaryData());
        $this->assertInstanceOf(Interfaces\Scheme::class, $uri->scheme);
        $this->assertInstanceOf(Interfaces\DataPath::class, $uri->path);
    }

    public function validStringUri()
    {
        return [
            'simple string' => [
                'uri' => 'data:text/plain;charset=us-ascii,Bonjour%20le%20monde%21',
                'mimetype' => 'text/plain',
                'parameters' => 'charset=us-ascii',
                'mediatype' => 'text/plain;charset=us-ascii',
                'data' => 'Bonjour%20le%20monde%21',
                'isBinaryData' => false,
            ],
            'string without mimetype' => [
                'uri' => 'data:,Bonjour%20le%20monde%21',
                'mimetype' => 'text/plain',
                'parameters' => 'charset=us-ascii',
                'mediatype' => 'text/plain;charset=us-ascii',
                'data' => 'Bonjour%20le%20monde%21',
                'isBinaryData' => false,
            ],
            'string without parameters' => [
                'uri' => 'data:text/plain,Bonjour%20le%20monde%21',
                'mimetype' => 'text/plain',
                'parameters' => 'charset=us-ascii',
                'mediatype' => 'text/plain;charset=us-ascii',
                'data' => 'Bonjour%20le%20monde%21',
                'isBinaryData' => false,
            ],
            'empty string' => [
                'uri' => 'data:,',
                'mimetype' => 'text/plain',
                'parameters' => 'charset=us-ascii',
                'mediatype' => 'text/plain;charset=us-ascii',
                'data' => '',
                'isBinaryData' => false,
            ],
            'binary data' => [
                'uri' => 'data:image/gif;charset=binary;base64,R0lGODlhIAAgAIABAP8AAP///yH+EUNyZWF0ZWQgd2l0aCBHSU1QACH5BAEKAAEALAAAAAAgACAAAAI5jI+py+0Po5y02ouzfqD7DwJUSHpjSZ4oqK7m5LJw/Ep0Hd1dG/OuvwKihCVianbbKJfMpvMJjWYKADs=',
                'mimetype' => 'image/gif',
                'parameters' => 'charset=binary',
                'mediatype' => 'image/gif;charset=binary',
                'data' => 'R0lGODlhIAAgAIABAP8AAP///yH+EUNyZWF0ZWQgd2l0aCBHSU1QACH5BAEKAAEALAAAAAAgACAAAAI5jI+py+0Po5y02ouzfqD7DwJUSHpjSZ4oqK7m5LJw/Ep0Hd1dG/OuvwKihCVianbbKJfMpvMJjWYKADs=',
                'isBinaryData' => true,
            ],
        ];
    }

    /**
     * @dataProvider invalidDataUriString
     * @expectedException InvalidArgumentException
     * @param $str
     */
    public function testCreateFromStringFailed($str)
    {
        DataUri::createFromString($str);
    }

    public function invalidDataUriString()
    {
        return [
            'boolean' => [true],
            'integer' => [23],
            'invalid format' => ['foo:bar'],
            'invalid data' => ['data:image/png;base64,°28'],
            'invalid data 2' => ['data:image/png;base64,zzz28'],
            'invalid mime type' => ['data:image_png;base64,zzz'],
            'invalid parameter' => ['data:image/png;base64;base64,zzz'],
        ];
    }

    /**
     * @dataProvider invalidDataUriPath
     * @expectedException RuntimeException
     * @param $path
     */
    public function testCreateFromPathFailed($path)
    {
        DataUri::createFromPath($path);
    }

    public function invalidDataUriPath()
    {
        return [
            'boolean' => [true],
            'integer' => [23],
            'invalid format' => ['/usr/bin/yeah'],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateFromComponentsFailedWithInvalidArgumentException()
    {
        DataUri::createFromComponents(parse_url('data:image/png;base64,°28'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateFromComponentsFailedInvalidMediatype()
    {
        DataUri::createFromString('data:image/png;base64,dsqdfqfd#fragment');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateFromComponentsFailedWithRuntimeException()
    {
        DataUri::createFromString('data:text/plain;charset=us-ascii,Bonjour%20le%20monde%21#fragment');
    }

    public function testWithPath()
    {
        $path = 'text/plain;charset=us-ascii,Bonjour%20le%20monde%21';
        $uri = DataUri::createFromString('data:'.$path);
        $this->assertSame($uri, $uri->withPath($path));
    }

    /**
     * @dataProvider validFilePath
     * @param $path
     * @param $expected
     */
    public function testCreateFromPath($path, $expected)
    {
        $uri = DataUri::createFromPath(dirname(__DIR__).'/data/'.$path);
        $this->assertSame($expected, $uri->path->getMimeType());
    }

    public function validFilePath()
    {
        return [
            'text file' => ['hello-world.txt', 'text/plain'],
            'img file' => ['red-nose.gif', 'image/gif'],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidUri()
    {
        DataUri::createFromString('http:text/plain;charset=us-ascii,Bonjour%20le%20monde%21');
    }

    public function testSetState()
    {
        $uri = DataUri::createFromPath('test/data/red-nose.gif');
        $generateUri = eval('return '.var_export($uri, true).';');
        $this->assertEquals($uri, $generateUri);
    }
}
