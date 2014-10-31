<?php
/*
 * This file is part of the codeliner/psb-bernard-dispatcher.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 31.10.14 - 15:16
 */

namespace Prooph\ServiceBus\Message\Bernard;

use Bernard\Message;
use Prooph\ServiceBus\Message\StandardMessage;

/**
 * Class BernardMessage
 *
 * @package Prooph\ServiceBus\Message\Bernard
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class BernardMessage extends StandardMessage implements Message
{
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name();
    }
}
 