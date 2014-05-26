<?php

namespace League\Url\test;

use PHPUnit_Framework_TestCase;
use League\Url\Components\Path;

class PathTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testArrayAccess()
    {
        $path = new Path(null);
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
        $path['toto'] = 'comment ça va';
    }
}
