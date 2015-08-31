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

use League\Uri\Components\Query;
use League\Uri\Modifiers\Filters\Flag;
use League\Uri\Modifiers\Filters\ForCallable;

/**
 * Filter the query component key/pair
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class FilterQuery extends AbstractQueryModifier
{
    use ForCallable;
    use Flag;

    /**
     * New instance
     *
     * @param callable $callable
     * @param int      $flag
     */
    public function __construct(callable $callable, $flag = self::FILTER_USE_VALUE)
    {
        $this->callable = $callable;
        $this->flag = $this->filterFlag($flag);
    }

    /**
     * {@inheritdoc}
     */
    protected function modify($str)
    {
        return (string) (new Query($str))->filter($this->callable, $this->flag);
    }
}
