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
use Prooph\Common\Messaging\MessageHeader;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;

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

        if (! $message instanceof BernardMessage) throw new \InvalidArgumentException(sprintf(
            "Routing the message %s failed due to wrong message type",
            $envelope->getName()
        ));

        return array($this, "routeMessage");
    }

    /**
     * @param BernardMessage $message
     */
    public function routeMessage(BernardMessage $message)
    {
        $remoteMessage = $message->getRemteMessage();

        if ($remoteMessage->header()->type() === MessageHeader::TYPE_COMMAND) {
            $this->commandBus->dispatch($remoteMessage);
        } else {
            $this->eventBus->dispatch($remoteMessage);
        }
    }
}
 
