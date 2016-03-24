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

use League\Uri\Interfaces\Fragment as FragmentInterface;

/**
 * Value object representing a URI fragment component.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   1.0.0
 */
class Fragment extends AbstractComponent implements FragmentInterface
{
    /**
     * @inheritdoc
     */
    protected static $reservedCharactersRegex = "\!\$&'\(\)\*\+,;\=\:\/@\?";

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
            $component = FragmentInterface::DELIMITER.$component;
        }

        return $component;
    }
}
