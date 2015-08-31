<?php

namespace League\Uri\Test;

use League\Uri\Modifier;
use League\Uri\Modifiers\RemoveDotSegments;
use League\Uri\Schemes\Http as HttpUri;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @group modifier
 */
class ModifierTest extends TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorFailed()
    {
        $modifier = new Modifier([new RemoveDotSegments(), 'toto']);
    }

    public function testPipe()
    {
        $modifier = new Modifier([new RemoveDotSegments()]);
        $alt = $modifier->pipe(new RemoveDotSegments());
        $this->assertInstanceOf('League\Uri\Modifier', $alt);
        $this->assertNotEquals($alt, $modifier);
    }

    public function testInvoke()
    {
        $uri = HttpUri::createFromString('http://www.example.com/path/../to/the/./sky.php?kingkong=toto&foo=bar+baz#doc3');
        $modifier = new Modifier([new RemoveDotSegments()]);
        $newUri = $modifier->__invoke($uri);
        $this->assertInstanceOf('League\Uri\Schemes\Http', $newUri);
        $this->assertSame('/to/the/sky.php', $newUri->getPath());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvokeThrowException()
    {
        $uri = 'http://www.example.com/path/../to/the/./sky.php?kingkong=toto&foo=bar+baz#doc3';
        $modifier = new Modifier([new RemoveDotSegments()]);
        $newUri = $modifier->__invoke($uri);
    }
}
