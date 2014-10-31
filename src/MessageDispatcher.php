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
use Prooph\ServiceBus\Message\MessageDispatcherInterface;
use Prooph\ServiceBus\Message\MessageInterface;

/**
 * Class MessageDispatcher
 *
 * @package Prooph\ServiceBus\Message\Bernard
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class MessageDispatcher implements MessageDispatcherInterface
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
     * @param MessageInterface $message
     * @return void
     */
    public function dispatch(MessageInterface $message)
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
     * @param MessageInterface $message
     * @return BernardMessage
     */
    private function toBernardMessage(MessageInterface $message)
    {
        if ($message instanceof BernardMessage) return $message;

        return BernardMessage::fromArray($message->toArray());
    }
}
 