<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Types;

use InvalidArgumentException;

/**
 * Validate the string type
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
trait StringValidator
{
    /**
     * validate a string
     *
     * @param mixed $str the value to evaluate as a string
     *
     * @throws \InvalidArgumentException if the submitted data can not be converted to string
     *
     * @return string
     */
    protected function validateString($str)
    {
        if (is_string($str)) {
            return $str;
        }

        throw new InvalidArgumentException(sprintf(
            'Expected data to be a string; received "%s"',
            (is_object($str) ? get_class($str) : gettype($str))
        ));
    }
}
