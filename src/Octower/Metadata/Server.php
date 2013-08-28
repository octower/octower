<?php
/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Metadata;

use Octower\Script\Event;

class Server extends Context
{
    public static function getScriptEvents()
    {
        return array(
            Event::EVENT_POST_EXTRACT,
            Event::EVENT_PRE_ENABLE,
            Event::EVENT_POST_ENABLE,
            Event::EVENT_PRE_DISABLE,
            Event::EVENT_POST_DISABLE,
        );
    }
}