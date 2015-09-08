<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Schemes;

use League\Uri\Interfaces\Uri;
use League\Uri\Schemes\Generic\AbstractHierarchicalUri;

/**
 * Value object representing FTP Uri.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class Ftp extends AbstractHierarchicalUri implements Uri
{
    /**
     * {@inheritdoc}
     */
    protected static $supportedSchemes = [
        'ftp' => 21,
    ];

    /**
     * {@inheritdoc}
     */
    protected function isValid()
    {
        return empty($this->query->getUriComponent().$this->fragment->getUriComponent())
            && $this->isValidGenericUri()
            && $this->isValidHierarchicalUri();
    }
}
