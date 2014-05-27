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
        $query = new Query(null);
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
        $query[] = 'comment ça va';
    }

    /**
     * @expectedException RuntimeException
     */
    public function testRemove()
    {
        $query = new Query(null);
        $query->remove('toto');
    }
}
