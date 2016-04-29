<?php

namespace League\Uri\Test\Components;

use League\Uri\Components\UserInfo;
use League\Uri\Test\AbstractTestCase;

/**
 * @group userinfo
 */
class UserInfoTest extends AbstractTestCase
{
    /**
     * @supportsDebugInfo
     */
    public function testDebugInfo()
    {
        $component = new UserInfo('yolo', 'oloy');
        $this->assertInternalType('array', $component->__debugInfo());
        ob_start();
        var_dump($component);
        $res = ob_get_clean();
        $this->assertContains($component->__toString(), $res);
        $this->assertContains('userInfo', $res);
    }

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
            [new UserInfo(null, 'bar'), new UserInfo(null, 'coucou'), true],
        ];
    }

    /**
     * @dataProvider toArrayProvider
     *
     * @param string $login
     * @param string $pass
     * @param string $expected_user
     * @param string $expected_pass
     * @param string $expected_str
     */
    public function testToArray($login, $pass, $expected_user, $expected_pass, $expected_str)
    {
        $userinfo = new UserInfo($login, $pass);
        $this->assertSame($expected_user, $userinfo->getUser());
        $this->assertSame($expected_pass, $userinfo->getPass());
        $this->assertSame($expected_str, (string) $userinfo->__toString());
    }

    public function toArrayProvider()
    {
        return [
            ['login', 'pass', 'login', 'pass', 'login:pass'],
            ['login', null, 'login', '', 'login'],
            [null, null, '', '', ''],
            ['', null, '', '', ''],
            ['', '', '', '', ''],
            [null, 'pass', '', 'pass', ''],
        ];
    }
}
