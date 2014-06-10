<?php

namespace League\Url\test;

use StdClass;
use ArrayIterator;
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
        $path[23] = 'bar';
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

    public function testPath()
    {
        $path = new Path('/test/query.php');
        $path->prepend('master');
        $this->assertSame('master/test/query.php', $path->get());

        $path->remove('test');
        $this->assertSame('master/query.php', $path->get());

        $path->remove('toto');
        $this->assertSame('master/query.php', $path->get());

        $path->append('sullivent', 'master');
        $this->assertSame('master/sullivent/query.php', $path->get());

        $path->set(null);
        $path->append('/shop/checkout');
        $this->assertSame('shop/checkout', $path->get());

        $path->set(array('shop', 'rev iew'));
        $this->assertSame('shop/rev%20iew', $path->get());

        $path->append(new ArrayIterator(array('sullivent', 'wacowski')));
        $this->assertSame('shop/rev%20iew/sullivent/wacowski', $path->get());

        $path->prepend('master');
        $path->prepend('master');
        $this->assertSame('master/master/shop/rev%20iew/sullivent/wacowski', (string) $path);

        $path->append('slave', 'sullivent');
        $path->append('slave', 'sullivent');

        $this->assertSame('master/master/shop/rev%20iew/sullivent/slave/slave/wacowski', (string) $path);
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
     * @expectedException RuntimeException
     */
    public function testBadPath()
    {
        new Path(new StdClass);
    }
}
