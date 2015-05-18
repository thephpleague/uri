<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 4.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url;

use League\Url\Interfaces;

/**
* Value object representing the UserInfo part of an URL.
*
* @package League.url
* @since 4.0.0
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
     * Create a new instance of UserInfo
     *
     * @param User $user
     * @param Pass $pass
     */
    public function __construct($user = null, $pass = null)
    {
        $this->user = (new User())->withValue($user);
        $this->pass = (new Pass())->withValue($pass);
    }

    /**
     * clone the current instance
     */
    public function __clone()
    {
        $this->user = clone $this->user;
        $this->pass = clone $this->pass;
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
        return clone $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function getPass()
    {
        return clone $this->pass;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array_map(function ($value) {
            if (empty($value)) {
                return null;
            }
            return $value;
        }, [
            'user' => $this->user->__toString(),
            'pass' => $this->pass->__toString(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $info = $this->user->getUriComponent();
        if (empty($info)) {
            return '';
        }

        return $info.$this->pass->getUriComponent();
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $info = $this->__toString();
        if (! empty($info)) {
            $info .= '@';
        }

        return $info;
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(Interfaces\UrlPart $component)
    {
        return $component->getUriComponent() == $this->getUriComponent();
    }

    /**
     * {@inheritdoc}
     */
    public function withUser($user)
    {
        return $this->withComponent('user', $user);
    }


    /**
     * Returns an instance with the modified component
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified component
     *
     * @param string $name  the component to set
     * @param string $value the component value
     *
     * @return static
     */
    protected function withComponent($name, $value)
    {
        $value = $this->$name->withValue($value);
        if ($this->$name->sameValueAs($value)) {
            return $this;
        }
        $clone = clone $this;
        $clone->$name = $value;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPass($pass)
    {
        return $this->withComponent('pass', $pass);
    }
}
