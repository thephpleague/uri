<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.url
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Components;

use League\Uri\Interfaces\Pass as PassInterface;
use League\Uri\Interfaces\UriPart;
use League\Uri\Interfaces\User as UserInterface;
use League\Uri\Interfaces\UserInfo as UserInfoInterface;
use League\Uri\Types\ImmutablePropertyTrait;

/**
 * Value object representing the UserInfo part of an URI.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 *
 */
class UserInfo implements UserInfoInterface
{
    use ImmutablePropertyTrait;

    /**
     * User Component
     *
     * @var User
     */
    protected $user;

    /**
     * Pass Component
     *
     * @var Pass
     */
    protected $pass;

    /**
     * Create a new instance of UserInfo
     *
     * @param string $user
     * @param string $pass
     */
    public function __construct($user = null, $pass = null)
    {
        $this->user = !$user instanceof UserInterface ? new User($user) : $user;
        $this->pass = !$pass instanceof PassInterface ? new Pass($pass) : $pass;
        $this->assertValidObject();
    }

    /**
     * @inheritdoc
     */
    public function getUser()
    {
        return $this->user->__toString();
    }

    /**
     * @inheritdoc
     */
    public function getPass()
    {
        return $this->pass->__toString();
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        $userInfo = $this->user->__toString();
        if (empty($userInfo)) {
            return $userInfo;
        }

        $pass = $this->pass->__toString();
        if (!empty($pass)) {
            $userInfo .= UserInfoInterface::SEPARATOR.$pass;
        }

        return $userInfo;
    }

    /**
     * @inheritdoc
     */
    public function getUriComponent()
    {
        $component = $this->__toString();
        if (!empty($component)) {
            $component .= UserInfoInterface::DELIMITER;
        }

        return $component;
    }

    /**
     * @inheritdoc
     */
    public function sameValueAs(UriPart $component)
    {
        return $this->getUriComponent() === $component->getUriComponent();
    }

    /**
     * @inheritdoc
     */
    public function withUser($user)
    {
        return $this->withProperty('user', $user);
    }

    /**
     * @inheritdoc
     */
    public function withPass($pass)
    {
        return $this->withProperty('pass', $pass);
    }

    /**
     * @inheritdoc
     */
    protected function assertValidObject()
    {
    }
}
