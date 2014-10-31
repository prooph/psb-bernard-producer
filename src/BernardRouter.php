<?php
/*
 * This file is part of the codeliner/psb-bernard-dispatcher.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 31.10.14 - 15:34
 */

namespace Prooph\ServiceBus\Message\Bernard;

use Bernard\Envelope;
use Bernard\Router;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Message\MessageHeader;
use Prooph\ServiceBus\Message\MessageInterface;

/**
 * Class BernardRouter
 *
 * Routes incoming messages to a PSB command bus or event bus
 *
 * @package Prooph\ServiceBus\Message\Bernard
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class BernardRouter implements Router
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var EventBus
     */
    private $eventBus;

    public function __construct(CommandBus $commandBus, EventBus $eventBus)
    {
        $this->commandBus = $commandBus;
        $this->eventBus = $eventBus;
    }

    /**
     * Returns the right Receiver (callable) based on the Envelope.
     *
     * @param  Envelope $envelope
     * @throws \InvalidArgumentException
     * @return array
     */
    public function map(Envelope $envelope)
    {
        $message = $envelope->getMessage();

        if (! $message instanceof MessageInterface) throw new \InvalidArgumentException(sprintf(
            "Routing the message %s failed due to wrong message type",
            $message->getName()
        ));

        return array($this, "routeMessage");
    }

    /**
     * @param MessageInterface $message
     */
    public function routeMessage(MessageInterface $message)
    {
        if ($message->header()->type() === MessageHeader::TYPE_COMMAND) {
            $this->commandBus->dispatch($message);
        } else {
            $this->eventBus->dispatch($message);
        }
    }
}
 