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
namespace League\Uri\Schemes;

use League\Uri\Schemes\Generic\AbstractHierarchicalUri;

/**
 * Value object representing WS and WSS Uri.
 *
 * @package League.uri
 * @since   4.0.0
 */
class Ws extends AbstractHierarchicalUri
{
    /**
     * {@inheritdoc}
     */
    protected static $supportedSchemes = [
        'ws' => 80,
        'wss' => 443,
    ];

    /**
     * {@inheritdoc}
     */
    protected function isValid()
    {
        return $this->fragment->isEmpty()
            && $this->isValidGenericUri()
            && $this->isValidHierarchicalUri();
    }
}
