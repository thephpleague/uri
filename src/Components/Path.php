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

use League\Uri\Interfaces\Components\Path as PathInterface;

/**
 * Value object representing a URI path component.
 *
 * @package League.uri
 * @since  1.0.0
 */
class Path extends AbstractComponent implements PathInterface
{
    use RemoveDotSegmentsTrait;

    /**
     * {@inheritdoc}
     */
    protected static $invalidCharactersRegex = ',[?#],';
}
