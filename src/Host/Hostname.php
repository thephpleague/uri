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
namespace League\Uri\Host;

use InvalidArgumentException;
use Pdp;

/**
 * A Trait to validate a Hostname
 *
 * @package League.uri
 * @since   4.0.0
 */
trait Hostname
{
    /**
     * HierarchicalComponent delimiter
     *
     * @var string
     */
    protected static $delimiter = '.';

    /**
     * hostname subdomain
     *
     * @var string|null
     */
    protected $subdomain;

    /**
     * hostname registrable domain
     *
     * @var string|null
     */
    protected $registerableDomain;

    /**
     * hostname public suffix
     *
     * @var string|null
     */
    protected $publicSuffix;

    /**
     * Tells whether we have a valid suffix
     *
     * @var bool
     */
    protected $isPublicSuffixValid = false;

    /**
     * Tells whether we have a IDN or not
     * @var boolean
     */
    protected $isIdn = false;

    /**
     * Pdp Parser
     *
     * @var Pdp\Parser
     */
    protected static $parser;

    /**
     * {@inheritdoc}
     */
    public function getPublicSuffix()
    {
        return $this->publicSuffix;
    }

    /**
     * {@inheritdoc}
     */
    public function getRegisterableDomain()
    {
        return $this->registerableDomain;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubdomain()
    {
        return $this->subdomain;
    }

    /**
     * {@inheritdoc}
     */
    public function isPublicSuffixValid()
    {
        return $this->isPublicSuffixValid;
    }

    /**
     * {@inheritdoc}
     */
    public function isIdn()
    {
        return $this->isIdn;
    }

    /**
     * Validate a string only host
     *
     * @param string $str
     *
     * @throws InvalidArgumentException If the string failed to be a valid hostname
     *
     * @return array
     */
    protected function validateStringHost($str)
    {
        $host       = $this->lower($this->setIsAbsolute($str));
        $raw_labels = explode(static::$delimiter, $host);
        $labels     = array_map(function ($value) {
            return idn_to_ascii($value);
        }, $raw_labels);

        $this->assertValidHost($labels);

        $this->isIdn               = $raw_labels !== $labels;
        $info                      = $this->getHostnameParser()->parseHost($host);
        $this->subdomain           = $info->subdomain;
        $this->registerableDomain  = $info->registerableDomain;
        $this->publicSuffix        = $info->publicSuffix;
        $this->isPublicSuffixValid = $this->getHostnameParser()->isSuffixValid($host);

        return array_map(function ($label) {
            return idn_to_utf8($label);
        }, $labels);
    }

    /**
     * set the FQDN property
     *
     * @param string $str
     *
     * @return string
     */
    abstract protected function setIsAbsolute($str);

    /**
     * Convert to lowercase a string without modifying unicode characters
     *
     * @param string $str
     *
     * @return string
     */
    protected function lower($str)
    {
        $res = [];
        for ($i = 0, $length = mb_strlen($str, 'UTF-8'); $i < $length; $i++) {
            $char = mb_substr($str, $i, 1, 'UTF-8');
            if (ord($char) < 128) {
                $char = strtolower($char);
            }
            $res[] = $char;
        }

        return implode('', $res);
    }

    /**
     * Validate a String Label
     *
     * @param array $labels found host labels
     *
     * @throws InvalidArgumentException If the validation fails
     */
    protected function assertValidHost(array $labels)
    {
        $verifs = array_filter($labels, function ($value) {
            return '' !== trim($value);
        });

        if ($verifs !== $labels) {
            throw new InvalidArgumentException('Invalid Hostname, empty labels are not allowed');
        }

        $this->isValidLabelsCount($labels);
        $this->isValidContent($labels);
    }

    /**
     * Validated the Host Label Count
     *
     * @param array $data host labels
     *
     * @throws InvalidArgumentException If the validation fails
     */
    abstract protected function isValidLabelsCount(array $data = []);

    /**
     * Validated the Host Label Pattern
     *
     * @param array $data host labels
     *
     * @throws InvalidArgumentException If the validation fails
     */
    protected function isValidContent(array $data)
    {
        if (count(preg_grep('/^[0-9a-z]([0-9a-z-]{0,61}[0-9a-z])?$/i', $data, PREG_GREP_INVERT))) {
            throw new InvalidArgumentException('Invalid Hostname, some labels contain invalid characters');
        }
    }

    /**
     * Initialize and access a Pdp\Parser object
     *
     * @return Pdp\Parser
     */
    protected function getHostnameParser()
    {
        if (!static::$parser instanceof Pdp\Parser) {
            static::$parser = $this->newHostnameParser();
        }

        return static::$parser;
    }

    /**
     * Pdp Factory
     *
     * @return Pdp\Parser
     */
    protected function newHostnameParser()
    {
        return new Pdp\Parser((new Pdp\PublicSuffixListManager())->getList());
    }
}
