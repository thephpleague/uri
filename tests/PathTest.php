<?php

namespace League\Url\Test\Components;

use ArrayIterator;
use League\Url\Path;
use PHPUnit_Framework_TestCase;
use StdClass;

/**
 * @group components
 */
class PathTest extends PHPUnit_Framework_TestCase
{
    public function testPath()
    {
        $path = new Path('/test/query.php');
        $path->prepend('master');
        $this->assertSame('master/test/query.php', $path->get());

        $path->remove('test');
        $this->assertSame('master/query.php', $path->get());

        $path->remove('toto');
        $this->assertSame('master/query.php', $path->get());
        $path->remove('');
        $path->append('sullivent', 'master');
        $this->assertSame('master/sullivent/query.php', $path->get());

        $path->set(null);
        $path->append('/shop/checkout/');
        $this->assertSame('shop/checkout/', $path->get());

        $path->set(array('shop', 'rev iew', ''));
        $this->assertSame('shop/rev%20iew/', $path->get());

        $path->append(new ArrayIterator(array('sullivent', 'wacowski', '')));
        $this->assertSame('shop/rev%20iew//sullivent/wacowski/', $path->get());

        $path->prepend('master');
        $path->prepend('master');
        $this->assertSame('master/master/shop/rev%20iew//sullivent/wacowski/', (string) $path);

        $path->append('slave', 'sullivent');
        $path->append('slave', 'sullivent');

        $path->remove('');

        $this->assertSame('master/master/shop/rev%20iew/sullivent/slave/slave/wacowski/', (string) $path);
    }

    public function testRemove()
    {
        $path = new Path('/toto/le/heros/masson');
        $path->remove('toto');
        $this->assertSame('le/heros/masson', (string) $path);
        $path->remove('ros/masson');
        $this->assertSame('le/heros/masson', (string) $path);
        $path->remove('asson');
        $this->assertSame('le/heros/masson', (string) $path);
        $path->remove('/heros/masson');
        $this->assertSame('le', (string) $path);
        $path = new Path('/toto/le/heros/masson');
        $path->remove('le/heros');
        $this->assertSame('toto/masson', (string) $path);
    }

    public function testKeys()
    {
        $path = new Path(array('bar', 3, 'troll', 3));
        $this->assertCount(4, $path->keys());
        $this->assertCount(0, $path->keys('foo'));
        $this->assertSame(array(0), $path->keys('bar'));
        $this->assertCount(2, $path->keys('3'));
        $this->assertSame(array(1, 3), $path->keys('3'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testBadPath()
    {
        new Path(new StdClass());
    }

    public function testPrepend()
    {
        $path = new Path('/toto/toto/shoky/master');
        $path->prepend('foo', 'toto', 1);
        $this->assertSame('/toto/foo/toto/shoky/master', $path->getUriComponent());
    }

    public function testNormalize()
    {
        $path = new Path('/../a/./b/../b/%63/%7bfoo%7d');
        $newPath = $path->normalize();
        $this->assertInstanceOf('League\Url\Interfaces\PathInterface', $newPath);
        $this->assertSame('a/b/c/%7Bfoo%7D', (string) $newPath);
    }

    public function testGetSegment()
    {
        $path = new Path('/toto/le/heros/masson');
        $this->assertSame('toto', $path->getSegment(0));
        $this->assertNull($path->getSegment(23));
        $this->assertSame('foo', $path->getSegment(23, 'foo'));
    }

    public function testSetSegment()
    {
        $path = new Path('/toto/toto/shoky/master');
        $path->setSegment(0, 'slave');
        $this->assertSame('slave', $path->getSegment(0));
        $this->assertSame('slave/toto/shoky/master', (string) $path);
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testSetSegmentInvalidOffset()
    {
        $path = new Path('/toto/toto/shoky/master');
        $path->setSegment(23, 'foo');
    }

    public function testSegmentNormalization()
    {
        $path = new Path('/master/toto/a%c2%b1b');
        $this->assertSame('master/toto/a%C2%B1b', (string) $path);

        $path = new Path('/master/toto/%7Eetc');
        $this->assertSame('master/toto/~etc', (string) $path);
    }

    public function testSetSegmentRemoveOffsetWithNullAndEmptyValue()
    {
        $path = new Path('/toto/toto/shoky/master');
        $path->setSegment(0, null);
        $this->assertSame('toto/shoky/master', (string) $path);
        $path->setSegment(0, '');
        $this->assertSame('/shoky/master', $path->getUriComponent());
    }
}
