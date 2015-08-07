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
namespace League\Uri\Components;

use InvalidArgumentException;
use League\Uri\Interfaces\Components\Scheme as SchemeInterface;
use League\Uri\UriParser;

/**
 * Value object representing a URI scheme component.
 *
 * @package League.uri
 * @since 1.0.0
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
        if (!preg_match(UriParser::SCHEME_REGEXP, $scheme)) {
            throw new InvalidArgumentException(sprintf("Invalid Submitted scheme: '%s'", $scheme));
        }

        return strtolower($scheme);
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $component = $this->getContent();

        return null === $component ? '' : $component.':';
    }
}
