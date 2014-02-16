<?php

namespace Bakame\Url\Test\Components;

use Bakame\Url\Components\Query;
use PHPUnit_Framework_TestCase;

class QueryTest extends PHPUnit_Framework_TestCase
{
    private $query;

    public function setUp()
    {
        $this->query = new Query('foo=bar&bar=baz');
    }

    public function testConstructor()
    {
        $query = new Query('?foo=bar&bar=baz');
        $this->assertCount(2, $query);
    }

    public function testCountable()
    {
        $this->assertCount(2, $this->query);
    }

    public function testIterator()
    {
        foreach ($this->query as $key => $value) {
            $this->assertSame($value, $this->query[$key]);
        }
    }

    public function testSet()
    {
        $this->query->set(new Query('?toto=thehero&tata=thevillain'));
        $this->assertCount(4, $this->query);
    }

    public function testArrayAccess()
    {
        $expected = 'leheros';
        $this->assertNull($this->query['toto']);
        $this->query['toto'] = $expected;
        $this->assertSame($expected, $this->query['toto']);
        unset($this->query['toto']);
        $this->assertNull($this->query['toto']);
        $this->assertFalse(isset($this->query['toto']));
    }
}
