<?php

namespace League\Url\test;

use StdClass;
use PHPUnit_Framework_TestCase;
use League\Url\Components\Path;

class PathTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testArrayAccess()
    {
        $path = new Path;
        $path[] = 'leheros';
        $this->assertNull($path[5]);
        $this->assertSame('leheros', $path[0]);
        $this->assertSame('leheros', (string) $path);
        $path[0] = 'levilain';
        $path[1] = 'bar';
        $this->assertTrue(isset($path[1]));
        $this->assertCount(2, $path);
        $this->assertSame('levilain/bar', (string) $path);
        foreach ($path as $offset => $value) {
            $this->assertSame($value, $path[$offset]);
        }
        unset($path[0]);
        $this->assertNull($path[0]);
        $this->assertSame(array(1 => 'bar'), $path->toArray());
        $path['toto'] = 'comment ça va';
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

    public function testContains1()
    {
        $path = new Path(array('bar', 'troll', 3));
        $this->assertCount(0, $path->fetchKeys('foo'));
    }

    public function testContains2()
    {
        $path = new Path(array('bar', 'troll', 3));
        $this->assertSame(array(0), $path->fetchKeys('bar'));
    }

    public function testContains3()
    {
        $path = new Path(array('bar', 3, 'troll', 3));
        $this->assertCount(2, $path->fetchKeys('3'));
    }

    public function testContains4()
    {
        $path = new Path(array('bar', 'troll', 3));
        $this->assertSame(array(2), $path->fetchKeys('3'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testBadPath()
    {
        new Path(new StdClass);
    }
}
