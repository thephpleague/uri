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
namespace League\Uri;

/**
 * Value object representing the UserInfo part of an URI.
 *
 * @package League.uri
 * @since 4.0.0
 *
 */
class UserInfo implements Interfaces\UserInfo
{
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

    /*
     * Trait To get/set immutable value property
     */
    use Types\ImmutableProperty;

    /**
     * Create a new instance of UserInfo
     *
     * @param string $user
     * @param string $pass
     */
    public function __construct($user = '', $pass = '')
    {
        $this->user = !$user instanceof Interfaces\User ? new User($user) : $user;
        $this->pass = !$pass instanceof Interfaces\Pass ? new Pass($pass) : $pass;
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
        if ($this->user->isEmpty()) {
            return '';
        }

        if ($this->pass->isEmpty()) {
            return $this->user->__toString();
        }

        return $this->user->__toString() . ':' . $this->pass->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $info = $this->__toString();
        if (!empty($info)) {
            $info .= '@';
        }

        return $info;
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(Interfaces\UriPart $component)
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
