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

use Bernard\Envelope;
use Bernard\Serializer;
use Prooph\Common\Messaging\MessageConverter;
use Prooph\Common\Messaging\MessageDataAssertion;
use Prooph\Common\Messaging\MessageFactory;

/**
 * Class BernardSerializer
 *
 * @package Prooph\ServiceBus\Message\Bernard
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class BernardSerializer implements Serializer
{
    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var MessageConverter
     */
    private $messageConverter;

    /**
     * @param MessageFactory $messageFactory
     * @param MessageConverter $messageConverter
     */
    public function __construct(MessageFactory $messageFactory, MessageConverter $messageConverter)
    {
        $this->messageFactory = $messageFactory;
        $this->messageConverter = $messageConverter;
    }

    /**
     * @param  Envelope $envelope
     * @throws \InvalidArgumentException
     * @return string
     */
    public function serialize(Envelope $envelope)
    {
        $message = $envelope->getMessage();

        if (! $message instanceof BernardMessage) {
            throw new \InvalidArgumentException(sprintf(
            "Serialize message %s failed due to wrong message type",
            $message->getName()
        ));
        }

        $messageData = $this->messageConverter->convertToArray($message->getProophMessage());

        MessageDataAssertion::assert($messageData);

        $messageData['created_at'] = $message->getProophMessage()->createdAt()->format('Y-m-d\TH:i:s.u');

        return json_encode([
            'message'      => $messageData,
            'timestamp' => $envelope->getTimestamp(),
        ]);
    }

    /**
     * @param $serialized
     * @return Envelope
     */
    public function deserialize($serialized)
    {
        $data = json_decode($serialized, true);

        $messageData = $data['message'];

        $messageData['created_at'] = \DateTimeImmutable::createFromFormat(
            'Y-m-d\TH:i:s.u',
            $messageData['created_at'],
            new \DateTimeZone('UTC')
        );

        $proophMessage = $this->messageFactory->createMessageFromArray($messageData['message_name'], $messageData);

        $envelope = new Envelope(BernardMessage::fromProophMessage($proophMessage));

        bernard_force_property_value($envelope, 'timestamp', $data['timestamp']);

        return $envelope;
    }
}
