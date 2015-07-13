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
namespace League\Uri\Schemes;

use InvalidArgumentException;

/**
 * A trait to validate scheme
 *
 * @package League.url
 * @since   4.0.0
 */
trait Validator
{
    /**
     * Validate and format the submitted string scheme
     *
     * @param  string $scheme
     * @throws  InvalidArgumentException if the scheme is invalid
     *
     * @return  string
     */
    protected function validate($scheme)
    {
        if (!preg_match('/^[a-z][-a-z0-9+.]+$/i', $scheme)) {
            throw new InvalidArgumentException(sprintf("Invalid Submitted scheme: '%s'", $scheme));
        }

        return strtolower($scheme);
    }
}
