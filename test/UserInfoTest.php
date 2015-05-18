<?php

namespace League\Url\Test;

use League\Url\Pass;
use League\Url\User;
use League\Url\UserInfo;
use PHPUnit_Framework_TestCase;

/**
 * @group userinfo
 */
class UserInfoTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param $login
     * @param $pass
     * @param $expected
     * @dataProvider isEmptyProvider
     */
    public function testIsEmpty($login, $pass, $expected)
    {
        $this->assertSame($expected, (new UserInfo($login, $pass))->isEmpty());
    }

    public function isEmptyProvider()
    {
        return [
            ['login', 'pass', false],
            ['login', null, false],
            ['', '', true],
            [null, null, true],
            [null, 'pass', true],
            ['', 'pass', true],
        ];
    }

    public function testToArray()
    {
        $this->assertSame(['user' => 'login', 'pass' => 'pass'], (new UserInfo('login', 'pass'))->toArray());
        $this->assertSame(['user' => 'login', 'pass' => null], (new UserInfo('login', null))->toArray());
        $this->assertSame(['user' => 'login', 'pass' => null], (new UserInfo('login', ''))->toArray());
        $this->assertSame(['user' => null, 'pass' => null], (new UserInfo('', ''))->toArray());
        $this->assertSame(['user' => null, 'pass' => null], (new UserInfo())->toArray());
        $this->assertSame(['user' => null, 'pass' => 'pass'], (new UserInfo('', 'pass'))->toArray());
        $this->assertSame(['user' => null, 'pass' => 'pass'], (new UserInfo(null, 'pass'))->toArray());
    }
}
