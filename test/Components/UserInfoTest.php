<?php

namespace League\Uri\Test\Components;

use League\Uri\Components\UserInfo;
use League\Uri\Interfaces;
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
        $this->assertInstanceOf(Interfaces\User::class, $userinfo->user);
        $this->assertInstanceOf(Interfaces\Pass::class, $userinfo->pass);
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
            [new UserInfo(null, 'bar'), new UserInfo(null, 'coucou'), true],
        ];
    }

    /**
     * @dataProvider userInfoProvider
     */
    public function testConstructor($user, $pass, $expected_user, $expected_pass, $expected_str)
    {
        $userinfo = new UserInfo($user, $pass);
        $this->assertSame($expected_user, $userinfo->getUser());
        $this->assertSame($expected_pass, $userinfo->getPass());
        $this->assertSame($expected_str, (string) $userinfo);
    }

    public function userInfoProvider()
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

    /**
     * @dataProvider createFromStringProvider
     */
    public function testWithContent($str, $expected_user, $expected_pass, $expected_str)
    {
        $conn = (new UserInfo())->withContent($str);
        $this->assertSame($expected_user, $conn->getUser());
        $this->assertSame($expected_pass, $conn->getPass());
        $this->assertSame($expected_str, (string) $conn);
    }

    public function createFromStringProvider()
    {
        return [
            ['user:pass', 'user', 'pass', 'user:pass'],
            ['user:', 'user', '', 'user'],
            ['user', 'user', '', 'user'],
            [':pass', '', 'pass', ''],
            ['', '', '', ''],
        ];
    }

    public function testWithContentReturnSameInstance()
    {
        $conn = new UserInfo('');
        $this->assertSame($conn, $conn->withContent(':pass'));

        $conn = new UserInfo('user', 'pass');
        $this->assertSame($conn, $conn->withContent('user:pass'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithContentThrowsInvalidArgumentException()
    {
        (new UserInfo())->withContent([]);
    }
}
