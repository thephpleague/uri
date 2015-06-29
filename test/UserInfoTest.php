<?php

namespace League\Uri\Test;

use League\Uri\Pass;
use League\Uri\User;
use League\Uri\UserInfo;
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

    public function testGetterMethod()
    {
        $userinfo = new UserInfo();
        $this->assertInstanceof('League\Uri\User', $userinfo->user);
        $this->assertInstanceof('League\Uri\Pass', $userinfo->pass);
    }

    /**
     * @dataProvider sameValueAsProvider
     */
    public function testSameValueAs($userinfo, $userinfobis, $expected)
    {
        $this->assertSame($expected, $userinfo->sameValueAs($userinfobis));
    }

    public function sameValueAsProvider()
    {
        return [
            [new UserInfo(), new UserInfo('foo', 'bar'), false],
            [new UserInfo('foo', 'bar'), new UserInfo('foo', 'bar'), true],
            [new UserInfo('', 'bar'), new UserInfo('', 'coucou'), true],
        ];
    }

    /**
     * @dataProvider toArrayProvider
     */
    public function testToArray($login, $pass, $expected, $expected_user, $expected_pass, $expected_str)
    {
        $userinfo = new UserInfo($login, $pass);
        $this->assertSame($expected, $userinfo->toArray());
        $this->assertSame($expected_user, $userinfo->getUser());
        $this->assertSame($expected_pass, $userinfo->getPass());
        $this->assertSame($expected_str, $userinfo->__toString());
    }

    public function toArrayProvider()
    {
        return [
            ['login', 'pass', ['user' => 'login', 'pass' => 'pass'], 'login', 'pass', 'login:pass'],
            ['login', null,   ['user' => 'login', 'pass' => null], 'login', '', 'login'],
            ['login', '',     ['user' => 'login', 'pass' => null], 'login', '', 'login'],
            ['', '',          ['user' => null   , 'pass' => null], '', '', ''],
            [null, null,      ['user' => null   , 'pass' => null], '', '', ''],
            ['', 'pass',      ['user' => null   , 'pass' => null], '', 'pass', ''],
            [null, 'pass',    ['user' => null   , 'pass' => null], '', 'pass', ''],
        ];
    }
}
