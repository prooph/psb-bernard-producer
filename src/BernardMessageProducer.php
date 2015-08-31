<?php
/*
 * This file is part of the prooph/psb-bernard-dispatcher.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 10/31/14 - 03:08 PM
 */
namespace Prooph\ServiceBus\Message\Bernard;

use Bernard\Producer;
use Prooph\Common\Messaging\Message;
use Prooph\ServiceBus\Async\MessageProducer;
use Prooph\ServiceBus\Exception\InvalidArgumentException;
use Prooph\ServiceBus\Exception\RuntimeException;
use React\Promise\Deferred;

/**
 * Class BernardMessageProducer
 *
 * @package Prooph\ServiceBus\Message\Bernard
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class BernardMessageProducer implements MessageProducer
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
        if (null !== $queue) {
            $this->useQueue($queue);
        }
    }

    /**
     * @inheritdoc
     */
    public function __invoke(Message $message, Deferred $deferred = null)
    {
        if (null !== $deferred) {
            throw new RuntimeException(__CLASS__ . ' cannot handle query messages which require future responses.');
        }
        $this->bernardProducer->produce($this->toBernardMessage($message), $this->queue);
    }

    /**
     * @param string $name
     * @throws InvalidArgumentException
     */
    public function useQueue($name)
    {
        if (! is_string($name)) {
            throw new InvalidArgumentException("Queue name is not a string!");
        }

        $this->queue = $name;
    }

    /**
     * @param Message $message
     * @return BernardMessage
     */
    private function toBernardMessage(Message $message)
    {
        return BernardMessage::fromProophMessage($message);
    }
}
