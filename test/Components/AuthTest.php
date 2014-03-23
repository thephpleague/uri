<?php

namespace League\Url\Test\Components;

use League\Url\Components\Auth;
use PHPUnit_Framework_TestCase;

class AuthTest extends PHPUnit_Framework_TestCase
{

    public function testGetterSetter()
    {
        $auth = new Auth;
        $this->assertNull($auth->getUsername());
        $this->assertNull($auth->getPassword());
        $auth
            ->setUsername('jane')
            ->setPassword('maryjane');

        $this->assertSame('maryjane', $auth->getPassword());
        $this->assertSame('jane:maryjane', $auth->__toString());
    }

    public function testRemove()
    {
        $auth = new Auth('mary', 'jane');
        $auth->clear();
        $this->assertEmpty($auth->__toString());
    }
}
