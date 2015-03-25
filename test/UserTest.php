<?php

namespace League\Url\Test;

use League\Url\User;
use PHPUnit_Framework_TestCase;

/**
 * @group components
 */
class UserTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $user = new User(new User('toto'));
        $this->assertSame('toto', $user->get());
    }

    public function testGetUriComponent()
    {
        $user = new User('toto');
        $this->assertSame('toto', $user->getUriComponent());
    }

    public function testGetUriComponentWithEmptyData()
    {
        $user = new User();
        $this->assertSame('', $user->getUriComponent());
    }

    public function providerInvalidCharacters()
    {
        return [
            ['"bad"'],
            ['<not good>'],
            ['{broken}'],
            ['failure?'],
            ['`oops`'],
            ['\\slashy'],
        ];
    }

    /**
     * @dataProvider providerInvalidCharacters
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidCharacters($value)
    {
        $user = new User($value);
    }
}
