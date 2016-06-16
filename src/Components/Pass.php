<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.2.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Components;

use League\Uri\Interfaces\Pass as PassInterface;

/**
 * Value object representing a URI pass component.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   1.0.0
 */
class Pass extends AbstractComponent implements PassInterface
{
    /**
     * @inheritdoc
     */
    public function getContent()
    {
        if (null === $this->data) {
            return $this->data;
        }

        return $this->encode((string) $this->data, self::$subdelimChars.'\:');
    }

    /**
     * Return the decoded string representation of the component
     *
     * @return string
     */
    public function getValue()
    {
        return (string) $this->data;
    }
}
