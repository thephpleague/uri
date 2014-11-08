<?php

namespace League\Url\Test\Components;

use ArrayIterator;
use League\Url\Components\Query;
use PHPUnit_Framework_TestCase;
use StdClass;

/**
 * @group components
 */
class QueryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Query
     */
    protected $query;

    public function setUp()
    {
        $this->query = new Query('?kingkong=toto');
    }

    public function testSameValueAs()
    {
        $query_local = new Query();
        $this->assertFalse($query_local->sameValueAs($this->query));
        $query_local->set($this->query);
        $this->assertTrue($query_local->sameValueAs($this->query));
    }

    public function testModifyWithArray()
    {
        $this->query->modify(array('john' => 'doe the john'));
        $this->assertSame('kingkong=toto&john=doe%20the%20john', (string) $this->query);
    }

    public function testModifyWithArrayIterator()
    {
        $this->query->modify(new ArrayIterator(array('john' => 'doe the john')));
        $this->assertSame('kingkong=toto&john=doe%20the%20john', (string) $this->query);
    }

    public function testModifyWithQueryInterface()
    {
        $this->query->modify(new Query(array('foo' => 'bar')));
        $this->assertSame('kingkong=toto&foo=bar', (string) $this->query);
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
     * @expectedException \RuntimeException
     */
    public function testFailmodify()
    {
        $this->query->modify(new StdClass());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testArrayAccess()
    {
        $query = new Query();
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

    public function testCountableRecursive()
    {
        $query = new Query('rech[id_client]=&rech[login]=&rech[NOM]=&options[NOM][precise]=1&rech[PRENOM]=&options[PRENOM][precise]=1&rech[email]=&rech[foo]=12345&rech[ID_ACHAT]=');
        $this->assertCount(2, $query);
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

    public function testDotFromString()
    {
        $query = new Query('foo.bar=baz');
        $this->assertSame('foo.bar=baz', (string) $query);
    }

    public function testDotFromArray()
    {
        $query = new Query(array('foo.bar' => 'baz'));
        $this->assertSame('foo.bar=baz', (string) $query);
    }
}
