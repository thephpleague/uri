<?php

namespace League\Uri\test\Schemes;

use League\Uri;
use League\Uri\Schemes\Data as DataUri;
use League\Uri\Schemes\Http as HttpUri;
use PHPUnit_Framework_TestCase;

/**
 * @group data
 */
class DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSameValueAsFailed()
    {
        $uri = 'data:text/plain;charset=us-ascii,Bonjour%20le%20monde%21';
        DataUri::createFromString($uri)->sameValueAs($uri);
    }

    /**
     * @dataProvider validStringUri
     */
    public function testCreateFromString($str, $mimetype, $parameters, $data, $asArray, $isBinaryData)
    {
        $uri = DataUri::createFromString($str);
        $this->assertSame('data', $uri->getScheme());
        $this->assertSame($mimetype, $uri->getMimeType());
        $this->assertSame($parameters, $uri->getParameters());
        $this->assertSame($data, $uri->getData());
        $this->assertSame($asArray, $uri->toArray());
        $this->assertSame($isBinaryData, $uri->isBinaryData());
        $this->assertInstanceOf('League\Uri\Interfaces\Components\Scheme', $uri->scheme);
        $this->assertInstanceOf('League\Uri\Interfaces\Components\DataPath', $uri->path);
    }

    public function validStringUri()
    {
        return [
            'simple string' => [
                'uri' => 'data:text/plain;charset=us-ascii,Bonjour%20le%20monde%21',
                'mimetype' => 'text/plain',
                'parameters' => 'charset=us-ascii',
                'data' => 'Bonjour%20le%20monde%21',
                'asArray' => [
                    'scheme' => 'data',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'text/plain;charset=us-ascii,Bonjour%20le%20monde%21',
                    'query' => null,
                    'fragment' => null,
                ],
                'isBinaryData' => false,
            ],
            'string without mimetype' => [
                'uri' => 'data:,Bonjour%20le%20monde%21',
                'mimetype' => 'text/plain',
                'parameters' => 'charset=us-ascii',
                'data' => 'Bonjour%20le%20monde%21',
                'asArray' => [
                    'scheme' => 'data',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'text/plain;charset=us-ascii,Bonjour%20le%20monde%21',
                    'query' => null,
                    'fragment' => null,
                ],
                'isBinaryData' => false,
            ],
            'string without parameters' => [
                'uri' => 'data:text/plain,Bonjour%20le%20monde%21',
                'mimetype' => 'text/plain',
                'parameters' => 'charset=us-ascii',
                'data' => 'Bonjour%20le%20monde%21',
                'asArray' => [
                    'scheme' => 'data',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'text/plain;charset=us-ascii,Bonjour%20le%20monde%21',
                    'query' => null,
                    'fragment' => null,
                ],
                'isBinaryData' => false,
            ],
            'empty string' => [
                'uri' => 'data:,',
                'mimetype' => 'text/plain',
                'parameters' => 'charset=us-ascii',
                'data' => '',
                'asArray' => [
                    'scheme' => 'data',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'text/plain;charset=us-ascii,',
                    'query' => null,
                    'fragment' => null,
                ],
                'isBinaryData' => false,
            ],
            'binary data' => [
                'uri' => 'data:image/gif;charset=binary;base64,R0lGODlhIAAgAIABAP8AAP///yH+EUNyZWF0ZWQgd2l0aCBHSU1QACH5BAEKAAEALAAAAAAgACAAAAI5jI+py+0Po5y02ouzfqD7DwJUSHpjSZ4oqK7m5LJw/Ep0Hd1dG/OuvwKihCVianbbKJfMpvMJjWYKADs=',
                'mimetype' => 'image/gif',
                'parameters' => 'charset=binary',
                'data' => 'R0lGODlhIAAgAIABAP8AAP///yH+EUNyZWF0ZWQgd2l0aCBHSU1QACH5BAEKAAEALAAAAAAgACAAAAI5jI+py+0Po5y02ouzfqD7DwJUSHpjSZ4oqK7m5LJw/Ep0Hd1dG/OuvwKihCVianbbKJfMpvMJjWYKADs=',
                'asArray' => [
                    'scheme' => 'data',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'image/gif;charset=binary;base64,R0lGODlhIAAgAIABAP8AAP///yH+EUNyZWF0ZWQgd2l0aCBHSU1QACH5BAEKAAEALAAAAAAgACAAAAI5jI+py+0Po5y02ouzfqD7DwJUSHpjSZ4oqK7m5LJw/Ep0Hd1dG/OuvwKihCVianbbKJfMpvMJjWYKADs=',
                    'query' => null,
                    'fragment' => null,
                ],
                'isBinaryData' => true,
            ],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider invalidDataUriString
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
     * @expectedException \RuntimeException
     * @dataProvider invalidDataUriPath
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
     * @expectedException \InvalidArgumentException
     * @dataProvider invalidDataUriComponents
     */
    public function testCreateFromComponentsFailed($path)
    {
        DataUri::createFromComponents($path);
    }

    public function invalidDataUriComponents()
    {
        return [
            'HierarchicalURI' => [HttpUri::createFromString('http://www.example.com')->toArray()],
            'invalid DataUri format' => [parse_url('data:image/png;base64,°28')],
        ];
    }

    /**
     * @dataProvider validFilePath
     */
    public function testCreateFromPath($path, $expected)
    {
        $uri = DataUri::createFromPath(__DIR__.'/'.$path);
        $this->assertSame($expected, $uri->getMimeType());
    }

    public function validFilePath()
    {
        return [
            'text file' => ['hello-world.txt', 'text/plain'],
            'img file'  => ['red-nose.gif', 'image/gif'],
        ];
    }

    public function testWithParameters()
    {
        $uri = DataUri::createFromString('data:text/plain;charset=us-ascii,Bonjour%20le%20monde%21');

        $newUri = $uri->withParameters('charset=us-ascii');
        $this->assertTrue($newUri->sameValueAS($uri));
    }

    public function testWithParametersOnBinaryData()
    {
        $expected = 'charset=binary;foo=bar';
        $uri = DataUri::createFromPath(__DIR__.'/red-nose.gif');
        $newUri = $uri->withParameters($expected);
        $this->assertSame($expected, $newUri->getParameters());
    }

    /**
     * @dataProvider fileProvider
     */
    public function testToBinary($uri)
    {
        $this->assertTrue($uri->toBinary()->isBinaryData());
    }

    public function fileProvider()
    {
        return [
            [DataUri::createFromPath(__DIR__.'/red-nose.gif')],
            [DataUri::createFromString('data:text/plain;charset=us-ascii,Bonjour%20le%20monde%21')],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider invalidParameters
     */
    public function testUpdateParametersFailed($parameters)
    {
        $uri = DataUri::createFromString('data:text/plain;charset=us-ascii,Bonjour%20le%20monde%21');
        $uri->withParameters($parameters);
    }

    public function invalidParameters()
    {
        return [
            'can not modify binary flag' => ['base64=3'],
            'can not add non empty flag' => ['image/jpg'],
        ];
    }

    public function testBinarySave()
    {
        $newFilePath = __DIR__.'/temp.gif';
        $uri = DataUri::createFromPath(__DIR__.'/red-nose.gif');
        $res = $uri->save($newFilePath);
        $this->assertInstanceOf('\SplFileObject', $res);
        $this->assertTrue($uri->sameValueAs(DataUri::createFromPath($newFilePath)));

        // Ensure file handle of \SplFileObject gets closed.
        $res = null;
        unlink($newFilePath);
    }

    public function testRawSave()
    {
        $newFilePath = __DIR__.'/temp.txt';
        $uri = DataUri::createFromPath(__DIR__.'/hello-world.txt');
        $res = $uri->save($newFilePath);
        $this->assertInstanceOf('\SplFileObject', $res);
        $this->assertTrue($uri->sameValueAs(DataUri::createFromPath($newFilePath)));
        $data = file_get_contents($newFilePath);
        $this->assertSame(base64_encode($data), $uri->getData());

        // Ensure file handle of \SplFileObject gets closed.
        $res = null;
        unlink($newFilePath);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSaveFailedWithUnReachableFilePath()
    {
        DataUri::createFromPath(__DIR__.'/hello-world.txt')->save('/usr/bin/yolo', 'w');
    }

    public function testSameValueAs()
    {
        $mock = $this->getMock('Psr\Http\Message\UriInterface');
        $mock->method('__toString')->willReturn('http://www.example.com');

        $this->assertFalse(DataUri::createFromPath(__DIR__.'/hello-world.txt')->sameValueAs($mock));
    }

    public function testSameValueAsSimple()
    {
        $uri1 = DataUri::createFromPath(__DIR__.'/hello-world.txt');
        $uri2 = DataUri::createFromPath(__DIR__.'/red-nose.gif');
        $this->assertFalse($uri1->sameValueAs($uri2));
    }
}
