<?php

namespace League\Uri\Test\Components;

use League\Uri\Components\UserInfo;
use PHPUnit_Framework_TestCase;

/**
 * @group userinfo
 */
class UserInfoTest extends PHPUnit_Framework_TestCase
{
    public function testGetterMethod()
    {
        $userinfo = new UserInfo();
        $this->assertInstanceof('League\Uri\Interfaces\User', $userinfo->user);
        $this->assertInstanceof('League\Uri\Interfaces\Pass', $userinfo->pass);
    }

    /**
     * @dataProvider sameValueAsProvider
     * @param $userinfo
     * @param $userinfobis
     * @param $expected
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
    public function testToArray($login, $pass, $expected_user, $expected_pass, $expected_str)
    {
        $userinfo = new UserInfo($login, $pass);
        $this->assertSame($expected_user, $userinfo->getUser());
        $this->assertSame($expected_pass, $userinfo->getPass());
        $this->assertSame($expected_str, $userinfo->__toString());
    }

    public function toArrayProvider()
    {
        return [
            ['login', 'pass', 'login', 'pass', 'login:pass'],
            ['login', '', 'login', '', 'login'],
            ['', '', '', '', ''],
            ['', 'pass', '', 'pass', ''],
        ];
    }
}
