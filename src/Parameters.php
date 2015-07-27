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
namespace League\Uri;

/**
 * Value object representing a URI query component.
 *
 * @package League.uri
 * @since   1.0.0
 */
class Parameters extends AbstractParameterComponent implements Interfaces\Parameters
{
    protected static $separator = ';';

    protected static $delimiter = ';';
}
