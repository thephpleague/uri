<?php
/**
* Bakame.url - A lightweight Url Parser library
*
* @author Ignace Nyamagana Butera <nyamsprod@gmail.com>
* @copyright 2013 Ignace Nyamagana Butera
* @link https://github.com/nyamsprod/Bakame.url
* @license http://opensource.org/licenses/MIT
* @version 1.0.0
* @package Bakame.url
*
* MIT LICENSE
*
* Permission is hereby granted, free of charge, to any person obtaining
* a copy of this software and associated documentation files (the
* "Software"), to deal in the Software without restriction, including
* without limitation the rights to use, copy, modify, merge, publish,
* distribute, sublicense, and/or sell copies of the Software, and to
* permit persons to whom the Software is furnished to do so, subject to
* the following conditions:
*
* The above copyright notice and this permission notice shall be
* included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
* NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
* LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
* OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
* WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
namespace Bakame\Url\Components;

use RuntimeException;

/**
 *  A Class to manipulate URL segment like component
 *
 * @package Bakame.Url
 *
 */
class Host extends AbstractSegment
{

    public function __construct($str)
    {
        $this->init($str, '.');
    }

    /**
     * Set Host values
     * @param mixed   $value            a string OR an array representing the data to be inserted
     * @param string  $position         append or prepend where to insert the data in the host array
     * @param integer $valueBefore      the data where to append the $value
     * @param integer $valueBeforeIndex the occurenceIndex of $valueBefore if $valueBefore appears more than once
     *
     * @throws RuntimeException If numbers of component exceeds or is equals to 127
     * @throws RuntimeException If host length exceeds or is equals to 255
     *
     * @return self
     */
    public function set($value, $position = null, $valueBefore = null, $valueBeforeIndex = null)
    {
        $value = (array) $value;

        if (127 <= (count($this->data) + count($value))) {
            throw new RuntimeException('Host may have at maximum 127 parts');
        } elseif (strlen($this->__toString()) + strlen(implode($this->separator, $value)) + strlen($this->separator) > 255) {
            throw new RuntimeException('Host may have a maximum of 255 characters');
        }

        if ('prepend' !== $position) {
            $position = 'append';
        }

        return $this->$position($value, $valueBefore, $valueBeforeIndex);
    }
}
