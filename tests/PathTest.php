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

    public function testGetRelativePath()
    {
        $path = new Path('/toto/le/heros/masson');
        $other = new Path('/toto/le/heros/masson');
        $this->assertSame('', (string) $path->relativeTo($other));
        $this->assertEquals($path, $path->relativeTo());
    }

    public function testGetRelativePathDiff()
    {
        $path = new Path('/toto/');
        $other = new Path('/toto/le/heros/masson');
        $this->assertSame('../../../', (string) $path->relativeTo($other));
    }

    public function testPrepend()
    {
        $path = new Path('/toto/toto/shoky/master');
        $path->prepend('foo', 'toto', 1);
        $this->assertSame('/toto/foo/toto/shoky/master', $path->getUriComponent());
    }


    public function testGetSegment()
    {
        $host = new Path('/toto/le/heros/masson');
        $this->assertSame('toto', $host->getSegment(0));
        $this->assertNull($host->getSegment(23));
        $this->assertSame('foo', $host->getSegment(23, 'foo'));
    }
}
