<?php

namespace Bakame\Url;

class AuthTest extends \PHPUnit_Framework_TestCase
{

    public function testGetterSetter()
    {
        $auth = new Auth;
        $this->assertNull($auth->get('user'));
        $this->assertNull($auth->get('pass'));
        $auth
            ->set('user', 'jane')
            ->set('pass', 'maryjane')
            ->set(array('user' => 'john'));

        $this->assertSame('maryjane', $auth->get('pass'));
        $this->assertSame('john:maryjane@', $auth->__toString());
    }

    public function testRemove()
    {
        $auth = new Auth('mary', 'jane');
        $auth->clear();
        $this->assertEmpty($auth->__toString());
    }
}
