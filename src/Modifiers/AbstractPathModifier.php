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

/**
 * Abstract Class to modify the Path component 
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
abstract class AbstractPathModifier extends AbstractPartialUriModifier
{
    /**
     * @inheritdoc
     */
    public function __invoke($uri)
    {
        $this->assertUriObject($uri);

        return $uri->withPath($this->modify($uri->getPath()));
    }
}
