<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.1.1
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Components;

use InvalidArgumentException;
use League\Uri\Interfaces\Scheme as SchemeInterface;

/**
 * Value object representing a URI scheme component.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   1.0.0
 */
class Scheme extends AbstractComponent implements SchemeInterface
{
    /**
     * Validate and format the submitted string scheme
     *
     * @param  string                   $scheme
     * @throws InvalidArgumentException if the scheme is invalid
     *
     * @return string
     */
    protected function validate($scheme)
    {
        if (!preg_match(',^[a-z]([-a-z0-9+.]+)?$,i', $scheme)) {
            throw new InvalidArgumentException(sprintf("Invalid Submitted scheme: '%s'", $scheme));
        }

        return strtolower($scheme);
    }

    /**
     * Returns the instance string representation
     * with its optional URI delimiters
     *
     * @return string
     */
    public function getUriComponent()
    {
        $component = $this->__toString();
        if ('' !== $component) {
            $component .= SchemeInterface::DELIMITER;
        }

        return $component;
    }
}
