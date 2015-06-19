<?php
/**
 * This file is part of the League.url library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/thephpleague/url/
 * @version 4.0.0
 * @package League.url
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Url;

use InvalidArgumentException;

/**
 * Value object representing a URL pass component.
 *
 * @package League.url
 * @since  1.0.0
 */
class Pass extends AbstractComponent implements Interfaces\Component
{
    /**
     * {@inheritdoc}
     */
    protected function assertValideString($data)
    {
        if (strpos($data, '@') !== false) {
            throw new InvalidArgumentException('The URL pass component contains invalid characters');
        }
    }
}
