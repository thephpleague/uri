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
 * A class to normalize URI objects
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class Normalize extends AbstractUriModifier
{
    /**
     * @inheritdoc
     */
    public function __invoke($uri)
    {
        $this->assertUriObject($uri);

        static $modifier;
        if (!$modifier instanceof Pipeline) {
            $modifier = new Pipeline([
                new HostToAscii(),
                new RemoveDotSegments(),
                new KsortQuery(),
            ]);
        }

        return $modifier
            ->process($uri)
            ->withScheme(mb_strtolower($uri->getScheme(), 'UTF-8'));
    }
}
