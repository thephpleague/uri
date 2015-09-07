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
namespace League\Uri\Components;

use InvalidArgumentException;
use League\Uri\Interfaces\FtpPath as FtpPathInterface;

/**
 * Value object representing FTP Uri.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class FtpPath extends HierarchicalPath implements FtpPathInterface
{
    /**
     * Typecode Regular expression
     */
    protected static $typeRegex = ',^(?P<basename>.*);type=(?P<typecode>a|i|d)$,';

    /**
     * Typecode value
     *
     * @array
     */
    protected static $typecodeList = [
        'a' => self::FTP_TYPE_ASCII,
        'i' => self::FTP_TYPE_BINARY,
        'd' => self::FTP_TYPE_DIRECTORY,
        ''  => self::FTP_TYPE_EMPTY,
    ];

    /**
     * {@inheritdoc}
     */
    public function getTypecode()
    {
        if (preg_match(self::$typeRegex, $this->getBasename(), $matches)) {
            return self::$typecodeList[$matches['typecode']];
        }

        return self::FTP_TYPE_EMPTY;
    }

    /**
     * {@inheritdoc}
     */
    public function withTypecode($type)
    {
        if (!in_array($type, self::$typecodeList)) {
            throw new InvalidArgumentException('invalid typecode');
        }

        $basename = $this->getBasename();
        if (preg_match(self::$typeRegex, $basename, $matches)) {
            $basename = $matches['basename'];
        }

        $extension = array_search($type, self::$typecodeList);
        if (!empty($extension)) {
            $extension = ';type='.$extension;
        }

        return $this->replace(count($this) - 1, $basename.$extension);
    }
}
