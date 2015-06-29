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
namespace League\Uri\Interfaces;

/**
 * Value object representing the UserInfo part of an URL.
 *
 * @package League.url
 * @since   4.0.0
 * @see     https://tools.ietf.org/html/rfc3986#section-3.2.1
 *
 * @property-read User $user
 * @property-read Pass $pass
 */
interface UserInfo extends UrlPart
{
    /**
     * Return an array representation of the UserInfo part
     *
     * @return array
     */
    public function toArray();

    /**
     * Retrieve the user component of the URL User Info part
     *
     * @return string
     */
    public function getUser();

    /**
     * Retrieve the pass component of the URL User Info part
     *
     * @return string
     */
    public function getPass();

    /**
     * Return an instance with the specified user.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified user.
     *
     * An empty user is equivalent to removing the user information.
     *
     * @param string $user The user to use with the new instance.
     *
     * @throws \InvalidArgumentException for invalid user.
     *
     * @return static
     */
    public function withUser($user);

    /**
     * Return an instance with the specified password.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified password.
     *
     * An empty password is equivalent to removing the password.
     *
     * @param string $pass The password to use with the new instance.
     *
     * @throws \InvalidArgumentException for invalid password.
     *
     * @return static
     */
    public function withPass($pass);
}
