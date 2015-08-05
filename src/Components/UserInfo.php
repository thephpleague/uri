<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/uri/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.uri
 */
namespace League\Uri\Components;

use League\Uri\Interfaces\Components\Pass as PassInterface;
use League\Uri\Interfaces\Components\UriPart;
use League\Uri\Interfaces\Components\User as UserInterface;
use League\Uri\Interfaces\Components\UserInfo as UserInfoInterface;
use League\Uri\Types\ImmutablePropertyTrait;

/**
 * Value object representing the UserInfo part of an URI.
 *
 * @package League.uri
 * @since 4.0.0
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
    public function __construct($user = '', $pass = '')
    {
        $this->user = !$user instanceof UserInterface ? new User($user) : $user;
        $this->pass = !$pass instanceof PassInterface ? new Pass($pass) : $pass;
        $this->assertValidObject();
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return $this->user->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function isNull()
    {
        return $this->user->isNull();
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->user->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getPass()
    {
        return $this->pass->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        if ($this->user->isEmpty()) {
            return ['user' => null, 'pass' => null];
        }

        return [
            'user' => $this->user->__toString(),
            'pass' => ($this->pass->isEmpty()) ? null : $this->pass->__toString(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if ($this->user->isNull()) {
            return '';
        }

        $str = $this->user->__toString();
        if ($this->pass->isNull()) {
            return $str;
        }

        return $str.':'.$this->pass->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        return $this->isNull() ? '' : $this->__toString().'@';
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(UriPart $component)
    {
        return $this->getUriComponent() === $component->getUriComponent();
    }

    /**
     * {@inheritdoc}
     */
    public function withUser($user)
    {
        return $this->withProperty('user', $user);
    }

    /**
     * {@inheritdoc}
     */
    public function withPass($pass)
    {
        return $this->withProperty('pass', $pass);
    }
}
