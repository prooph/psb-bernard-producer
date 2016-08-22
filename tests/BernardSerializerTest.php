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
namespace ProophTest\ServiceBus;

use Bernard\Envelope;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\Common\Messaging\NoOpMessageConverter;
use Prooph\ServiceBus\Message\Bernard\BernardMessage;
use Prooph\ServiceBus\Message\Bernard\BernardSerializer;
use ProophTest\ServiceBus\Mock\DoSomething;

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
    public function it_serializes_and_unserializes_a_message()
    {
        $doSomethingCommand = new DoSomething(['data' => 'some test data']);

        $bernardMessage = BernardMessage::fromProophMessage($doSomethingCommand);

        $envelope = new Envelope($bernardMessage);

//        bernard_force_property_value($envelope, 'timestamp', 1);


        $bernardSerializer = new BernardSerializer(new FQCNMessageFactory(), new NoOpMessageConverter());

        $serializedEnvelope = $bernardSerializer->serialize($envelope);

        $unserializedEnvelope = $bernardSerializer->unserialize($serializedEnvelope);

        $this->assertEquals($envelope->getTimestamp(), $unserializedEnvelope->getTimestamp());
        $this->assertEquals(
            $envelope->getMessage()->getProophMessage()->toArray(),
            $unserializedEnvelope->getMessage()->getProophMessage()->toArray()
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
