<?php

namespace League\Uri\Test\Modifiers;

use InvalidArgumentException;
use League\Uri\Modifiers\Pipeline;
use League\Uri\Modifiers\RemoveDotSegments;
use League\Uri\Schemes\Http as HttpUri;
use PHPUnit_Framework_TestCase as TestCase;
use RuntimeException;

/**
 * @group pipeline
 */
class PipelineTest extends TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorFailed()
    {
        $pipeline = new Pipeline([new RemoveDotSegments(), 'toto']);
    }

    public function testPipe()
    {
        $pipeline = new Pipeline([new RemoveDotSegments()]);
        $alt = $pipeline->pipe(new RemoveDotSegments());
        $this->assertInstanceOf(Pipeline::class, $alt);
        $this->assertNotEquals($alt, $pipeline);
    }

    public function testInvoke()
    {
        $uri = HttpUri::createFromString('http://www.example.com/path/../to/the/./sky.php?kingkong=toto&foo=bar+baz#doc3');
        $pipeline = new Pipeline([new RemoveDotSegments()]);
        $newUri = $pipeline->process($uri);
        $this->assertInstanceOf(HttpUri::class, $newUri);
        $this->assertSame('/to/the/sky.php', $newUri->getPath());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvokeThrowInvalidArgumentException()
    {
        $uri = 'http://www.example.com/path/../to/the/./sky.php?kingkong=toto&foo=bar+baz#doc3';
        $pipeline = new Pipeline([new RemoveDotSegments()]);
        $newUri = $pipeline->process($uri);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testInvokeThrowRuntimeException()
    {
        $modifier = function (HttpUri $uri) {
            return true;
        };

        $uri = HttpUri::createFromString('http://www.example.com');
        (new Pipeline([$modifier]))->process($uri);
    }
}
