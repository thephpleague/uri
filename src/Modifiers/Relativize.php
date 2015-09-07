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
namespace League\Uri\Modifiers;

use League\Uri\Components\HierarchicalPath;
use League\Uri\Interfaces\Uri as LeagueUriInterface;
use League\Uri\Modifiers\Filters\Uri;
use Psr\Http\Message\UriInterface as Psr7UriInterface;

/**
 * Relativize an URI according to a base URI using
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class Relativize extends AbstractUriModifier
{
    use Uri;

    /**
     * @param string $str the path to relativize
     *
     * @return string the path relativize according to the base URI object
     */
    protected function modify($str)
    {
        $orig = new HierarchicalPath($this->uri->getPath());

        return $orig->relativize($orig->modify($str))->__toString();
    }

    /**
     * New instance
     *
     * @param LeagueUriInterface|Psr7UriInterface $uri
     */
    public function __construct($uri)
    {
        $this->uri = $this->filterUri($uri);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($relative)
    {
        $this->assertUriObject($relative);

        if ($this->uri->getScheme() !== $relative->getScheme()
            || $this->uri->getAuthority() !== $relative->getAuthority()
        ) {
            return $relative;
        }

        return $relative
            ->withScheme('')->withUserInfo('')->withPort('')->withHost('')
            ->withPath($this->modify($relative->getPath()));
    }
}
