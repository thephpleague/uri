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
    protected static $typeRegex = ',^(?P<basename>.*);type=(?P<typecode>a|i|d)$,';

    /**
     * {@inheritdoc}
     */
    protected static $supportedSchemes = [
        'ftp' => 21,
    ];

    protected static $typecodeList = [
        'a' => self::TYPE_ASCII,
        'i' => self::TYPE_BINARY,
        'd' => self::TYPE_DIRECTORY,
        ''  => self::TYPE_NONE,
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
        if (preg_match(self::$typeRegex, $this->path->getBasename(), $matches)) {
            return self::$typecodeList[$matches['typecode']];
        }

        return self::TYPE_NONE;
    }

    /**
     * {@inheritdoc}
     */
    public function withTypecode($type)
    {
        if (!in_array($type, self::$typecodeList)) {
            throw new InvalidArgumentException('invalid typecode');
        }

        $basename = $this->path->getBasename();
        if (preg_match(self::$typeRegex, $basename, $matches)) {
            $basename = $matches['basename'];
        }

        $extension = array_search($type, self::$typecodeList);
        if (!empty($extension)) {
            $extension = ';type='.$extension;
        }

        return $this->withProperty('path', $this->path->replace(count($this->path) - 1, $basename.$extension));
    }
}
