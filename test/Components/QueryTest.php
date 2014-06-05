<?php

namespace League\Url\test;

use PHPUnit_Framework_TestCase;
use League\Url\Components\Query;
use ArrayIterator;
use StdClass;

class QueryTest extends PHPUnit_Framework_TestCase
{
    protected $query;

    public function setUp()
    {
        $this->query = new Query('?kingkong=toto');
    }

    public function testModifyWithArray()
    {
        $this->query->modify(array('john' => 'doe the john'));
        $this->assertSame('kingkong=toto&john=doe+the+john', (string) $this->query);
    }

    public function testModifyWithArrayIterator()
    {
        $this->query->modify(new ArrayIterator(array('john' => 'doe the john')));
        $this->assertSame('kingkong=toto&john=doe+the+john', (string) $this->query);
    }

    public function testModifyWithString()
    {
        $this->query->modify('?kingkong=tata');
        $this->assertSame('kingkong=tata', (string) $this->query);
    }

    public function testModifyWithEmptyString()
    {
        $this->query->modify('');
        $this->assertSame('kingkong=toto', (string) $this->query);
    }

    public function testModifyWithRemoveArg()
    {
        $this->query->modify(array('kingkong' => null));
        $this->assertSame('', (string) $this->query);
    }

    public function testSetterWithNull()
    {
        $this->query->set(null);
        $this->assertNull($this->query->get());
        $this->assertSame('', (string) $this->query);
    }

    public function testSetterWithArray()
    {
        $this->query->set(array('ali' => 'baba'));
        $this->assertSame('ali=baba', (string) $this->query);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testFailmodify()
    {
        $this->query->modify(new StdClass);
    }

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
     * @expectedException InvalidArgumentException
     */
    public function testEnctype()
    {
        $query = new Query;
        $this->assertSame(Query::PHP_QUERY_RFC1738, $query->getEncodingType());
        $query->setEncodingType(Query::PHP_QUERY_RFC3986);
        $this->assertSame(Query::PHP_QUERY_RFC3986, $query->getEncodingType());
        $query->setEncodingType(34);
    }

    /**
     * @requires PHP 5.4
     * @expectedException InvalidArgumentException
     */
    public function testEnctypePHP54()
    {
        $query = new Query;
        $this->assertSame(PHP_QUERY_RFC1738, $query->getEncodingType());
        $query->setEncodingType(PHP_QUERY_RFC3986);
        $this->assertSame(PHP_QUERY_RFC3986, $query->getEncodingType());
        $query->setEncodingType(34);
    }

    public function testKeys()
    {
        $query = new Query(array('foo' => 'bar', 'baz' => 'troll', 'lol' => 3, 'toto' => 'troll'));
        $this->assertCount(0, $query->keys('foo'));
        $this->assertSame(array('foo'), $query->keys('bar'));
        $this->assertCount(0, $query->keys('3'));
        $this->assertSame(array('lol'), $query->keys(3));
        $this->assertSame(array('baz', 'toto'), $query->keys('troll'));
    }
}
