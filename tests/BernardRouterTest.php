<?php
/*
 * This file is part of the prooph/psb-bernard-producer.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 9/1/15 - 9:02 AM
 */
namespace Prooph\ServiceBusTest;

use Bernard\Envelope;
use Prooph\Common\Messaging\Message;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Message\Bernard\BernardMessage;
use Prooph\ServiceBus\Message\Bernard\BernardRouter;

/**
 * Class BernardRouterTest
 *
 * @package Prooph\ServiceBusTest
 */
final class BernardRouterTest extends TestCase
{
    /**
     * @test
     */
    function it_dispatches_a_command_on_the_command_bus()
    {
        $proophMessage = $this->prophesize(Message::class);

        $proophMessage->messageType()->willReturn('command');

        $commandBus = $this->prophesize(CommandBus::class);
        $eventBus = $this->prophesize(EventBus::class);

        $commandBus->dispatch($proophMessage->reveal())->shouldBeCalled();
        $eventBus->dispatch($proophMessage->reveal())->shouldNotBeCalled();

        $bernardMessage = BernardMessage::fromProophMessage($proophMessage->reveal());

        $bernardRouter = new BernardRouter($commandBus->reveal(), $eventBus->reveal());

        $bernardRouter->routeMessage($bernardMessage);
    }

    /**
     * @test
     */
    function it_dispatches_an_event_on_the_event_bus()
    {
        $proophMessage = $this->prophesize(Message::class);

        $proophMessage->messageType()->willReturn('event');

        $commandBus = $this->prophesize(CommandBus::class);
        $eventBus = $this->prophesize(EventBus::class);

        $commandBus->dispatch($proophMessage->reveal())->shouldNotBeCalled();
        $eventBus->dispatch($proophMessage->reveal())->shouldBeCalled();

        $bernardMessage = BernardMessage::fromProophMessage($proophMessage->reveal());

        $bernardRouter = new BernardRouter($commandBus->reveal(), $eventBus->reveal());

        $bernardRouter->routeMessage($bernardMessage);
    }

    /**
     * @test
     */
    function it_maps_envelope_to_route_message_method()
    {
        $bernardMessage = $this->prophesize(BernardMessage::class);

        $envelope = new Envelope($bernardMessage->reveal());

        $commandBus = $this->prophesize(CommandBus::class);
        $eventBus = $this->prophesize(EventBus::class);

        $bernardRouter = new BernardRouter($commandBus->reveal(), $eventBus->reveal());

        $routingCallback = $bernardRouter->map($envelope);

        $this->assertSame([$bernardRouter, 'routeMessage'], $routingCallback);
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Exception\InvalidArgumentException
     */
    function it_throws_exception_if_envelope_does_not_contain_a_prooph_message_wrapper()
    {
        $nonProophMessage = $this->prophesize(\Bernard\Message::class);

        $envelope = new Envelope($nonProophMessage->reveal());

        $commandBus = $this->prophesize(CommandBus::class);
        $eventBus = $this->prophesize(EventBus::class);

        $bernardRouter = new BernardRouter($commandBus->reveal(), $eventBus->reveal());

        $bernardRouter->map($envelope);
    }
}
 