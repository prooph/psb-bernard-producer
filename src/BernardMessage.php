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
use Prooph\Common\Messaging\RemoteMessage;

/**
 * Class BernardMessage
 *
 * @package Prooph\ServiceBus\Message\Bernard
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class BernardMessage implements Message
{
    /**
     * @var RemoteMessage
     */
    private $remoteMessage;

    public static function fromRemoteMessage(RemoteMessage $remoteMessage) {
        $instance = new self();
        $instance->remoteMessage = $remoteMessage;
        return $instance;
    }
    /**
     * @return string
     */
    public function getName()
    {
        return $this->remoteMessage->name();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->remoteMessage->toArray();
    }

    /**
     * @return RemoteMessage
     */
    public function getRemteMessage()
    {
        return $this->remoteMessage;
    }
}
 