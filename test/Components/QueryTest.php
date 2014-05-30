<?php

namespace League\Url\test;

use PHPUnit_Framework_TestCase;
use League\Url\Components\Query;

class QueryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testArrayAccess()
    {
        $query = new Query;
        $query['toto'] = 'leheros';
        $this->assertNull($query['tata']);
        $this->assertSame('leheros', $query['toto']);
        $this->assertSame('toto=leheros', (string) $query);
        $query['toto'] = 'levilain';
        $query['foo'] = 'bar';
        $this->assertTrue(isset($query['foo']));
        $this->assertCount(2, $query);
        $this->assertSame('toto=levilain&foo=bar', (string) $query);
        foreach ($query as $offset => $value) {
            $this->assertSame($value, $query[$offset]);
        }
        unset($query['toto']);
        $this->assertNull($query['toto']);
        $this->assertSame(array('foo' => 'bar'), $query->toArray());
        $query[] = 'comment ça va';
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testRemove()
    {
        $query = new Query;
        $query->remove('toto');
    }

    public function testContains()
    {
        $query = new Query(array('foo' => 'bar', 'baz' => 'troll', 'lol' => 3, 'toto' => 'troll'));
        $this->assertCount(0, $query->fetchKeys('foo'));
        $this->assertSame(array('foo'), $query->fetchKeys('bar'));
        $this->assertCount(0, $query->fetchKeys('3'));
        $this->assertSame(array('lol'), $query->fetchKeys(3));
        $this->assertSame(array('baz', 'toto'), $query->fetchKeys('troll'));
    }
}
