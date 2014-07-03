<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 3.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url\Components;

use RuntimeException;
use True\Punycode;

/**
 *  A class to manipulate URL Host component
 *
 *  @package League.url
 *  @since  1.0.0
 */
class Host extends AbstractSegment implements HostInterface
{
    /**
     * {@inheritdoc}
     */
    protected $delimiter = '.';

    /**
     * Punycode Alogrithm Object
     *
     * @var True\Punycode
     */
    protected $punycode;

    /**
     * Environment Internal encoding
     * @var string
     */
    protected $encoding = 'utf-8';

    /**
     * Alter the Environment Internal Encoding if it is not utf-8
     *
     * @return void
     */
    protected function saveInternalEncoding()
    {
        $this->encoding = mb_internal_encoding();
        if (stripos($this->encoding, 'utf-8') === false) {
            mb_internal_encoding('utf-8');
        }
    }

    /**
     * Restore the Environment Internal Encoding
     *
     * @return void
     */
    protected function restoreInternalEncoding()
    {
        mb_internal_encoding($this->encoding);
    }

    /**
     * {@inheritdoc}
     */
    public function __construct($data = null)
    {
        $this->punycode = new Punycode;
        $this->encoding = mb_internal_encoding();
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (! $this->data) {
            return null;
        }

        return implode(
            $this->delimiter,
            array_map(array($this->punycode, 'encode'), $this->data)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function prepend($data, $whence = null, $whence_index = null)
    {
        $this->data = $this->prependSegment(
            $this->data,
            $this->validate($data, $this->data),
            $whence,
            $whence_index
        );
    }

    /**
     * {@inheritdoc}
     */
    public function append($data, $whence = null, $whence_index = null)
    {
        $this->data = $this->appendSegment(
            $this->data,
            $this->validate($data, $this->data),
            $whence,
            $whence_index
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toAscii()
    {
        return $this->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function toUnicode()
    {
        $this->saveInternalEncoding();
        $res = $this->punycode->decode($this->__toString());
        $this->restoreInternalEncoding();

        return $res;
    }

    /**
     * Validate Host data before insertion into a URL host component
     *
     * @param mixed $data the data to insert
     * @param array $host an array representation of a host component
     *
     * @return array
     *
     * @throws RuntimeException If the added is invalid
     */
    protected function validate($data, array $host = array())
    {
        $data = $this->validateSegment($data, $this->delimiter);
        if (! $data) {
            return $data;
        }

        //the 63 length must be checked before unicode application
        $this->saveInternalEncoding();
        $res = array_filter($data, function ($label) {
            return mb_strlen($label) > 63;
        });
        if (count($res)) {
            $this->restoreInternalEncoding();
            throw new RuntimeException('Invalid hostname, check its length');
        }

        $data = explode(
            $this->delimiter,
            $this->punycode->encode(implode($this->delimiter, $data))
        );

        $res = preg_grep('/^[0-9a-z]([0-9a-z-]{0,61}[0-9a-z])?$/i', $data, PREG_GREP_INVERT);
        if (count($res)) {
            $this->restoreInternalEncoding();
            throw new RuntimeException('Invalid host label, check its content');
        }

        $imploded = implode($this->delimiter, $data);
        $nb_labels = count($host) + count($data);
        if (count($data) && (2 > $nb_labels || 127 <= $nb_labels)) {
            throw new RuntimeException('Host may have between 2 and 127 parts');
        } elseif (225 <= (strlen(implode($this->delimiter, $host)) + strlen($imploded) + 1)) {
            throw new RuntimeException('Host may have a maximum of 255 characters');
        }

        $data = $this->sanitizeValue($data);

        $data = explode(
            $this->delimiter,
            $this->punycode->decode(implode($this->delimiter, $data))
        );
        $this->restoreInternalEncoding();

        return $data;
    }
}
