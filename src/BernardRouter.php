<?php
/*
 * This file is part of the prooph/psb-bernard-producer.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 10/31/14 - 03:08 PM
 */

namespace Prooph\ServiceBus\Message\Bernard;

use Bernard\Envelope;
use Bernard\Router;
use Prooph\Common\Messaging\Message;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Exception\InvalidArgumentException;

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
     * @throws InvalidArgumentException
     * @return array
     */
    public function map(Envelope $envelope)
    {
        $message = $envelope->getMessage();

        if (! $message instanceof BernardMessage) {
            throw new InvalidArgumentException(sprintf(
            "Routing the message %s failed due to wrong message type",
            $envelope->getName()
        ));
        }

        return [$this, "routeMessage"];
    }

    /**
     * @param BernardMessage $message
     */
    public function routeMessage(BernardMessage $message)
    {
        $proophMessage = $message->getProophMessage();

        if ($proophMessage->messageType() === Message::TYPE_COMMAND) {
            $this->commandBus->dispatch($proophMessage);
        } else {
            $this->eventBus->dispatch($proophMessage);
        }
    }
}
