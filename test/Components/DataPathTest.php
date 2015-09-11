<?php

namespace League\Uri\Test\Components;

use InvalidArgumentException;
use League\Uri\Components\DataPath as Path;
use PHPUnit_Framework_TestCase;
use RuntimeException;

/**
 * @group data
 */
class DataPathTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider invalidDataUriPath
     * @expectedException RuntimeException
     * @param $path
     */
    public function testCreateFromPathFailed($path)
    {
        Path::createFromPath($path);
    }

    public function invalidDataUriPath()
    {
        return [
            'boolean' => [true],
            'integer' => [23],
            'invalid format' => ['/usr/bin/yeah'],
        ];
    }

    public function testWithPath()
    {
        $uri = new Path('text/plain;charset=us-ascii,Bonjour%20le%20monde%21');
        $this->assertSame($uri, $uri->modify($uri));
    }

    /**
     * @dataProvider validFilePath
     * @param $path
     * @param $expected
     */
    public function testCreateFromPath($path, $expected)
    {
        $uri = Path::createFromPath(dirname(__DIR__).'/data/'.$path);
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
        $uri = new Path('text/plain;charset=us-ascii,Bonjour%20le%20monde%21');
        $newUri = $uri->withParameters('charset=us-ascii');
        $this->assertSame($newUri, $uri);
    }

    public function testWithParametersOnBinaryData()
    {
        $expected = 'charset=binary;foo=bar';
        $uri = Path::createFromPath(dirname(__DIR__).'/data/red-nose.gif');
        $newUri = $uri->withParameters($expected);
        $this->assertSame($expected, $newUri->getParameters());
    }

    /**
     * @dataProvider fileProvider
     * @param $uri
     */
    public function testToBinary($uri)
    {
        $this->assertTrue($uri->toBinary()->isBinaryData());
    }

    /**
     * @dataProvider fileProvider
     * @param $uri
     */
    public function testToAscii($uri)
    {
        $this->assertFalse($uri->toAscii()->isBinaryData());
    }

    public function fileProvider()
    {
        return [
            'with a file' => [Path::createFromPath(dirname(__DIR__).'/data/red-nose.gif')],
            'with a text' => [new Path('text/plain;charset=us-ascii,Bonjour%20le%20monde%21')],
        ];
    }

    /**
     * @dataProvider invalidParameters
     * @expectedException InvalidArgumentException
     * @param $parameters
     */
    public function testUpdateParametersFailed($parameters)
    {
        $uri = new Path('text/plain;charset=us-ascii,Bonjour%20le%20monde%21');
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
        $newFilePath = dirname(__DIR__).'/data/temp.gif';
        $uri = Path::createFromPath(dirname(__DIR__).'/data/red-nose.gif');
        $res = $uri->save($newFilePath);
        $this->assertInstanceOf('\SplFileObject', $res);
        $res = null;
        $this->assertSame((string) $uri, (string) Path::createFromPath($newFilePath));

        // Ensure file handle of \SplFileObject gets closed.
        $res = null;
        unlink($newFilePath);
    }

    public function testRawSave()
    {
        $newFilePath = dirname(__DIR__).'/data/temp.txt';
        $uri = Path::createFromPath(dirname(__DIR__).'/data/hello-world.txt');
        $res = $uri->save($newFilePath);
        $this->assertInstanceOf('\SplFileObject', $res);
        $this->assertSame((string) $uri, (string) Path::createFromPath($newFilePath));
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
        Path::createFromPath(dirname(__DIR__).'/data/hello-world.txt')->save('/usr/bin/yolo', 'w');
    }

    public function testDataPathConstructor()
    {
        $this->assertSame('text/plain;charset=us-ascii,', (string) new Path());
    }
}
