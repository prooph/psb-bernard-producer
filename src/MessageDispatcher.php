<?php
/*
 * This file is part of the codeliner/psb-bernard-dispatcher.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 31.10.14 - 15:08
 */
namespace Prooph\ServiceBus\Message\Bernard;

use Bernard\Producer;
use Prooph\Common\Messaging\RemoteMessage;
use Prooph\ServiceBus\Message\RemoteMessageDispatcher;

/**
 * Class MessageDispatcher
 *
 * @package Prooph\ServiceBus\Message\Bernard
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class MessageDispatcher implements RemoteMessageDispatcher
{
    /**
     * @var Producer
     */
    private $bernardProducer;

    /**
     * @var string
     */
    private $queue;

    /**
     * @param Producer $bernardProducer
     * @param string|null $queue
     */
    public function __construct(Producer $bernardProducer, $queue = null)
    {
        $this->bernardProducer = $bernardProducer;
        if (! is_null($queue)) $this->useQueue($queue);
    }

    /**
     * @param RemoteMessage $message
     * @return void
     */
    public function dispatch(RemoteMessage $message)
    {
        $this->bernardProducer->produce($this->toBernardMessage($message), $this->queue);
    }

    /**
     * @param string $name
     * @throws \InvalidArgumentException
     */
    public function useQueue($name)
    {
        if (! is_string($name)) throw new \InvalidArgumentException("Queue name is not a string!");

        $this->queue = $name;
    }

    /**
     * @param RemoteMessage $message
     * @return BernardMessage
     */
    private function toBernardMessage(RemoteMessage $message)
    {
        return BernardMessage::fromRemoteMessage($message);
    }
}
 