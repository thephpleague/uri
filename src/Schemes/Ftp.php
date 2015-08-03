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

use InvalidArgumentException;
use League\Uri\Interfaces;

/**
 * Value object representing FTP Uri.
 *
 * @package League.uri
 * @since   4.0.0
 *
 */
class Ftp extends Generic\AbstractHierarchicalUri implements Interfaces\Schemes\Ftp
{
    /**
     * {@inheritdoc}
     */
    protected static $supportedSchemes = [
        'ftp' => 21,
    ];

    /**
     * Typecode Regular expression detection
     *
     * @var string
     */
    protected static $typeCodeRegex = ',^(?P<basename>.*);type=(?P<typecode>a|i|d)$,';

    /**
     * {@inheritdoc}
     */
    protected function isValid()
    {
        if (!empty($this->fragment->__toString().$this->query->__toString())) {
            return false;
        }

        return $this->isValidHierarchicalUri();
    }

    /**
     * {@inheritdoc}
     */
    public function getTypecode()
    {
        if (preg_match(static::$typeCodeRegex, $this->path->getBasename(), $matches)) {
            return $matches['typecode'];
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function withTypecode($type)
    {
        $extension = trim($type);
        if (!in_array($extension, ['a', 'i', 'd', ''])) {
            throw new InvalidArgumentException('invalid typecode');
        }

        $basename = $this->path->getBasename();
        if (preg_match(static::$typeCodeRegex, $basename, $matches)) {
            $basename = $matches['basename'];
        }

        if (!empty($extension)) {
            $extension = ';type='.$extension;
        }

        return $this->withProperty('path', $this->path->replace(count($this->path) - 1, $basename.$extension));
    }
}
