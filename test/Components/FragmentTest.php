<?php

namespace League\Uri\Test\Components;

use InvalidArgumentException;
use League\Uri\Components\Fragment;
use League\Uri\Test\AbstractTestCase;

/**
 * @group fragment
 */
class FragmentTest extends AbstractTestCase
{
    /**
     * @dataProvider getUriComponentProvider
     */
    public function testGetUriComponent($str, $encoded)
    {
        $this->assertSame($encoded, (new Fragment($str))->getUriComponent());
    }

    public function getUriComponentProvider()
    {
        $unreserved = 'a-zA-Z0-9.-_~!$&\'()*+,;=:@';

        return [
            'null' => [null, ''],
            'empty' => ['', '#'],
            'evaluate empty' => ['0', '#0'],
            'hash' => ['#', '#%23'],
            'toofan' => ['toofan', '#toofan'],
            'notencoded' => ["azAZ0-9/?-._~!$&'()*+,;=:@", '#azAZ0-9/?-._~!$&\'()*+,;=:@'],
            'encoded' => ['%^[]{}"<>\\', '#%25%5E%5B%5D%7B%7D%22%3C%3E%5C'],
            'Percent encode spaces' => ['frag ment', '#frag%20ment'],
            'Percent encode multibyte' => ['€', '#%E2%82%AC'],
            "Don't encode something that's already encoded" => ['frag%20ment', '#frag%20ment'],
            'Percent encode invalid percent encodings' => ['frag%2-ment', '#frag%252-ment'],
            "Don't encode path segments" => ['frag/ment', '#frag/ment'],
            "Don't encode unreserved chars or sub-delimiters" => [$unreserved, '#'.$unreserved],
            'Encoded unreserved chars are not decoded' => ['fr%61gment', '#fr%61gment'],
        ];
    }

    /**
     * @dataProvider geValueProvider
     */
    public function testGetValue($str, $expected)
    {
        $this->assertSame($expected, (new Fragment($str))->getDecoded());
    }

    public function geValueProvider()
    {
        return [
            [null, null],
            ['', ''],
            ['0', '0'],
            ['azAZ0-9/?-._~!$&\'()*+,;=:@%^/[]{}\"<>\\', 'azAZ0-9/?-._~!$&\'()*+,;=:@%^/[]{}\"<>\\'],
            ['€', '€'],
            ['%E2%82%AC', '€'],
            ['frag ment', 'frag ment'],
            ['frag%20ment', 'frag ment'],
            ['frag%2-ment', 'frag%2-ment'],
            ['fr%61gment', 'fr%61gment'],
        ];
    }

    /**
     * @param $str
     * @expectedException InvalidArgumentException
     * @dataProvider failedConstructor
     */
    public function testInvalidFragment($str)
    {
        new Fragment($str);
    }

    public function failedConstructor()
    {
        return [
            'bool' => [true],
            'Std Class' => [(object) 'foo'],
            'float' => [1.2],
            'array' => [['foo']],
        ];
    }

    /**
     * @supportsDebugInfo
     */
    public function testDebugInfo()
    {
        $component = new Fragment('yolo');
        $this->assertInternalType('array', $component->__debugInfo());
        ob_start();
        var_dump($component);
        $res = ob_get_clean();
        $this->assertContains($component->__toString(), $res);
        $this->assertContains('fragment', $res);
    }

    public function testPreserverDelimiter()
    {
        $fragment = new Fragment();
        $altFragment = $fragment->modify(null);
        $this->assertSame($fragment, $altFragment);
        $this->assertNull($altFragment->getContent());
        $this->assertSame('', $altFragment->__toString());
    }
}
