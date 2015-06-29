<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/url/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/url/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.url
 */
namespace League\Uri;

/**
 * Value object representing the UserInfo part of an URL.
 *
 * @package League.url
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

    /**
     * Trait To get/set immutable value property
     */
    use Components\ImmutableProperty;

    /**
     * Create a new instance of UserInfo
     *
     * @param string $user
     * @param string $pass
     */
    public function __construct($user = null, $pass = null)
    {
        $this->user = new User($user);
        $this->pass = new Pass($pass);
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

        return $this->user->__toString().':'.$this->pass->__toString();
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
    public function sameValueAs(Interfaces\UrlPart $component)
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
