<?php

/*
* This file is part of Octower.
*
* (c) William Pottier <developer@william-pottier.fr>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Octower\Util;

class ArrayCallbackIterator extends \ArrayIterator
{
    private $callback;

    public function __construct($value, $callback)
    {
        parent::__construct($value);
        $this->callback = $callback;
    }

    public function current()
    {
        $value = parent::current();

        return call_user_func($this->callback, $value);
    }
}
