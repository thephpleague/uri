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

    /**
     * @param $login
     * @param $pass
     * @param $expected
     * @dataProvider toArrayProvider
     */
    public function testToArray($login, $pass, $expected)
    {
        $this->assertSame($expected, (new UserInfo($login, $pass))->toArray());
    }

    public function toArrayProvider()
    {
        return [
            ['login', 'pass', ['user' => 'login', 'pass' => 'pass']],
            ['login', null,   ['user' => 'login', 'pass' => '']],
            ['login', '',     ['user' => 'login', 'pass' => '']],
            ['', '',          ['user' => ''     , 'pass' => '']],
            [null, null,      ['user' => ''     , 'pass' => '']],
            ['', 'pass',      ['user' => ''     , 'pass' => 'pass']],
            [null, 'pass',    ['user' => ''     , 'pass' => 'pass']],
        ];
    }
}
