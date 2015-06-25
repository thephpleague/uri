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
namespace League\Url;

use InvalidArgumentException;

/**
 * Value object representing a URL pass component.
 *
 * @package League.url
 * @since  1.0.0
 */
class Pass extends AbstractComponent implements Interfaces\Component
{
    /**
     * {@inheritdoc}
     */
    protected function assertValidString($data)
    {
        if (strpos($data, '@') !== false) {
            throw new InvalidArgumentException('The URL pass component contains invalid characters');
        }
    }
}
