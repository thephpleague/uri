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
use League\Uri\Modifiers\Filters\Keys;

/**
 * Remove segments from the URI path
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class RemoveSegments extends AbstractPathModifier
{
    use Keys;

    /**
     * New instance
     *
     * @param callable|int[] $keys
     */
    public function __construct(array $keys)
    {
        $this->keys = $keys;
    }

    /**
     * {@inheritdoc}
     */
    protected function modify($str)
    {
        return (string) (new HierarchicalPath($str))->without($this->keys);
    }
}
