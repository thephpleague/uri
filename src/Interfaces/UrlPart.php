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
 * Value object representing simple URL part.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package  League.url
 * @since  4.0.0
 */
interface UrlPart
{
    /**
     * Returns true if the URL part considered empty
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Returns the URL part string representation
     *
     * @return string
     */
    public function __toString();

    /**
     * Returns the URL part string representation
     * with its optional URL delimiters
     *
     * @return string
     */
    public function getUriComponent();

    /**
     * Returns whether two URL part represent the same value
     * The Comparaison is based on the getUriComponent method
     *
     * @param UrlPart $component
     *
     * @return bool
     */
    public function sameValueAs(UrlPart $component);
}
