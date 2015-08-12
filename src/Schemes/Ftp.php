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

use InvalidArgumentException;
use League\Uri\Interfaces\Schemes\Ftp as FtpInterface;
use League\Uri\Schemes\Generic\AbstractHierarchicalUri;

/**
 * Value object representing FTP Uri.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class Ftp extends AbstractHierarchicalUri implements FtpInterface
{
    /**
     * Typecode Regular expression
     */
    const TYPECODE_REGEXP = ',^(?P<basename>.*);type=(?P<typecode>a|i|d)$,';

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
        return empty($this->fragment->__toString().$this->query->__toString())
            && $this->isValidGenericUri()
            && $this->isValidHierarchicalUri();
    }

    /**
     * {@inheritdoc}
     */
    public function getTypecode()
    {
        if (preg_match(self::TYPECODE_REGEXP, $this->path->getBasename(), $matches)) {
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
        if (preg_match(self::TYPECODE_REGEXP, $basename, $matches)) {
            $basename = $matches['basename'];
        }

        if (!empty($extension)) {
            $extension = ';type='.$extension;
        }

        return $this->withProperty('path', $this->path->replace(count($this->path) - 1, $basename.$extension));
    }

    /**
     * {@inheritdoc}
     */
    public function withExtension($extension)
    {
        $typecode = $this->getTypecode();
        if (empty($typecode)) {
            return parent::withExtension($extension);
        }
        preg_match(self::TYPECODE_REGEXP, $this->path->getBasename(), $matches);

        return $this->withProperty(
            'path',
            $this->path
                ->replace(count($this->path) - 1, $matches['basename'])->withExtension($extension)
        )->withTypecode($typecode);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        $typecode = $this->getTypecode();
        $extension = $this->path->getExtension();
        if (empty($typecode)) {
            return $extension;
        }

        return substr($extension, 0, -strlen(';type='.$typecode));
    }
}
