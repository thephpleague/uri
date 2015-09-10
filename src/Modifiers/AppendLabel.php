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

use League\Uri\Modifiers\Filters\Label;

/**
 * Append a label to the URI host
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class AppendLabel extends AbstractHostModifier
{
    use Label;

    /**
     * New instance
     *
     * @param string $label the data to be used
     *
     */
    public function __construct($label)
    {
        $this->label = $this->filterLabel($label);
    }

    /**
     * {@inheritdoc}
     */
    protected function modify($str)
    {
        return $this->label->modify($str)->append($this->label)->__toString();
    }
}
