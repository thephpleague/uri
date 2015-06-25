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
 * Value object representing a URL user component.
 *
 * @package League.url
 * @since  1.0.0
 */
class User extends AbstractComponent implements Interfaces\Component
{
    /**
     * {@inheritdoc}
     */
    protected function assertValidString($data)
    {
        if (preg_match('/[:@]/', $data)) {
            throw new InvalidArgumentException('The URL user component can not contain the URL pass component');
        }
    }
}
