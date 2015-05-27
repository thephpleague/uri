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
namespace League\Url\Interfaces;

/**
 * Value object representing the UserInfo part of an URL.
 *
 * @package League.url
 * @since 4.0.0
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
     * @return Component
     */
    public function getUser();

    /**
     * Retrieve the pass component of the URL User Info part
     *
     * @return Component
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
     * @return self A new instance with the specified user.
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
     * @return self A new instance with the specified password.
     */
    public function withPass($pass);
}
