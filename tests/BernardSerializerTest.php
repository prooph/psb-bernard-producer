<?php
/*
 * This file is part of the prooph/psb-bernard-producer.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 9/1/15 - 9:18 AM
 */
namespace Prooph\ServiceBusTest;

use Bernard\Envelope;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\Common\Messaging\NoOpMessageConverter;
use Prooph\ServiceBus\Message\Bernard\BernardMessage;
use Prooph\ServiceBus\Message\Bernard\BernardSerializer;
use Prooph\ServiceBusTest\Mock\DoSomething;

/**
 * Class BernardSerializerTest
 *
 * @package Prooph\ServiceBusTest
 */
final class BernardSerializerTest extends TestCase
{
    /**
     * @test
     */
    public function it_serializes_and_deserializes_a_message()
    {
        $doSomethingCommand = new DoSomething(['data' => 'some test data']);

        $bernardMessage = BernardMessage::fromProophMessage($doSomethingCommand);

        $envelope = new Envelope($bernardMessage);

        bernard_force_property_value($envelope, 'timestamp', 1);

        $bernardSerializer = new BernardSerializer(new FQCNMessageFactory(), new NoOpMessageConverter());

        $serializedEnvelope = $bernardSerializer->serialize($envelope);

        $deserializedEnvelope = $bernardSerializer->deserialize($serializedEnvelope);

        $this->assertEquals($envelope->getTimestamp(), $deserializedEnvelope->getTimestamp());
        $this->assertEquals(
            $envelope->getMessage()->getProophMessage()->toArray(),
            $deserializedEnvelope->getMessage()->getProophMessage()->toArray()
        );
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Exception\InvalidArgumentException
     */
    public function it_throws_exception_if_envelope_does_not_contain_a_prooph_message_wrapper()
    {
        $nonProophMessage = $this->prophesize(\Bernard\Message::class);

        $envelope = new Envelope($nonProophMessage->reveal());

        $bernardSerializer = new BernardSerializer(new FQCNMessageFactory(), new NoOpMessageConverter());

        $bernardSerializer->serialize($envelope);
    }
}
