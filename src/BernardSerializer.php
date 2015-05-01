<?php
/*
 * This file is part of the codeliner/psb-bernard-dispatcher.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 31.10.14 - 15:23
 */

namespace Prooph\ServiceBus\Message\Bernard;

use Bernard\Envelope;
use Bernard\Serializer;
use Prooph\Common\Messaging\RemoteMessage;

/**
 * Class BernardSerializer
 *
 * @package Prooph\ServiceBus\Message\Bernard
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class BernardSerializer implements Serializer
{

    /**
     * @param  Envelope $envelope
     * @throws \InvalidArgumentException
     * @return string
     */
    public function serialize(Envelope $envelope)
    {
        $message = $envelope->getMessage();

        if (! $message instanceof BernardMessage) throw new \InvalidArgumentException(sprintf(
            "Serialize message %s failed due to wrong message type",
            $message->getName()
        ));

        return json_encode(array(
            'message'      => $message->toArray(),
            'timestamp' => $envelope->getTimestamp(),
        ));
    }

    /**
     * @param $serialized
     * @return Envelope
     */
    public function deserialize($serialized)
    {
        $data = json_decode($serialized, true);

        $envelope = new Envelope(BernardMessage::fromRemoteMessage(RemoteMessage::fromArray($data['message'])));

        bernard_force_property_value($envelope, 'timestamp', $data['timestamp']);

        return $envelope;
    }
}
 