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
namespace League\Uri\Schemes;

use League\Uri\Interfaces\Uri;
use League\Uri\Schemes\Generic\AbstractHierarchicalUri;

/**
 * Value object representing WS and WSS Uri.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class Ws extends AbstractHierarchicalUri implements Uri
{
    /**
     * @inheritdoc
     */
    protected static $supportedSchemes = [
        'ws' => 80,
        'wss' => 443,
    ];

    /**
     * @inheritdoc
     */
    protected function isValid()
    {
        return '' === $this->fragment->getUriComponent()
            && $this->isValidGenericUri()
            && $this->isValidHierarchicalUri();
    }
}
