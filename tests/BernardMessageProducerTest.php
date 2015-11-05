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
namespace ProophTest\ServiceBus;

use Bernard\Consumer;
use Bernard\Doctrine\MessagesSchema;
use Bernard\Driver\DoctrineDriver;
use Bernard\Middleware\MiddlewareBuilder;
use Bernard\Producer;
use Bernard\QueueFactory\PersistentFactory;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\NoOpMessageConverter;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Message\Bernard\BernardMessage;
use Prooph\ServiceBus\Message\Bernard\BernardRouter;
use Prooph\ServiceBus\Message\Bernard\BernardSerializer;
use Prooph\ServiceBus\Message\Bernard\BernardMessageProducer;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;
use Prooph\ServiceBus\Plugin\Router\EventRouter;
use ProophTest\ServiceBus\Mock\DoSomething;
use ProophTest\ServiceBus\Mock\MessageHandler;
use ProophTest\ServiceBus\Mock\SomethingDone;
use Prophecy\Argument;
use React\Promise\Deferred;

/**
 * Class BernardMessageProducerTest
 *
 * @package Prooph\ServiceBusTest
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class BernardMessageProducerTest extends TestCase
{
    /**
     * @var Producer
     */
    private $bernardProducer;

    /**
     * @var PersistentFactory
     */
    private $persistentFactory;

    protected function setUp()
    {
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'dbname' => ':memory:'
        ]);

        $schema = new Schema();

        MessagesSchema::create($schema);

        array_map([$connection, "executeQuery"], $schema->toSql($connection->getDatabasePlatform()));

        $doctrineDriver = new DoctrineDriver($connection);

        //Use the serializer provided by psb-bernard-dispatcher
        $this->persistentFactory = new PersistentFactory(
            $doctrineDriver,
            new BernardSerializer(
                new FQCNMessageFactory(),
                new NoOpMessageConverter()
            )
        );

        $this->bernardProducer = new Producer($this->persistentFactory, new MiddlewareBuilder());
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Exception\InvalidArgumentException
     */
    public function it_does_not_allow_empty_queue_name()
    {
        new BernardMessageProducer($this->bernardProducer, '');
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Exception\InvalidArgumentException
     */
    public function it_does_not_allow_non_string_queue_name()
    {
        new BernardMessageProducer($this->bernardProducer, 123);
    }

    /**
     * @test
     */
    public function it_allows_null_as_queue()
    {
        $bernardProducer = $this->prophesize(Producer::class);
        $bernardProducer->produce(Argument::type(BernardMessage::class), null)->shouldBeCalled();
        $proophBernardProducer = new BernardMessageProducer($bernardProducer->reveal());

        $message = $this->prophesize(Message::class);

        $proophBernardProducer($message->reveal());
    }

    /**
     * @test
     */
    public function it_sends_a_command_to_queue_pulls_it_with_consumer_and_forwards_it_to_command_bus()
    {
        $command = new DoSomething(['data' => 'test command']);

        //The message dispatcher works with a ready-to-use bernard producer and one queue
        $messageProducer = new BernardMessageProducer($this->bernardProducer, 'test-queue');

        //Normally you would send the command on a command bus. We skip this step here cause we are only
        //interested in the function of the message dispatcher
        $messageProducer($command);

        //Set up command bus which will receive the command message from the bernard consumer
        $consumerCommandBus = new CommandBus();

        $doSomethingHandler = new MessageHandler();

        $consumerCommandBus->utilize(new CommandRouter([
            $command->messageName() => $doSomethingHandler
        ]));

        //We use a special bernard router which forwards all messages to a command bus or event bus depending on the
        //Prooph\ServiceBus\Message\MessageHeader::TYPE
        $bernardRouter = new BernardRouter($consumerCommandBus, new EventBus());

        $bernardConsumer = new Consumer($bernardRouter, new MiddlewareBuilder());

        //We use the same queue name here as we've defined for the message dispatcher above
        $bernardConsumer->tick($this->persistentFactory->create('test-queue'));

        $this->assertNotNull($doSomethingHandler->getLastMessage());

        $this->assertEquals($command->payload(), $doSomethingHandler->getLastMessage()->payload());
    }

    /**
     * @test
     */
    public function it_sends_an_event_to_queue_pulls_it_with_consumer_and_forwards_it_to_event_bus()
    {
        $event = new SomethingDone(['data' => 'test event']);

        //The message dispatcher works with a ready-to-use bernard producer and one queue
        $messageProducer = new BernardMessageProducer($this->bernardProducer, 'test-queue');

        //Normally you would send the event on a event bus. We skip this step here cause we are only
        //interested in the function of the message dispatcher
        $messageProducer($event);

        //Set up event bus which will receive the event message from the bernard consumer
        $consumerEventBus = new EventBus();

        $somethingDoneListener = new MessageHandler();

        $consumerEventBus->utilize(new EventRouter([
            $event->messageName() => [$somethingDoneListener]
        ]));

        //We use a special bernard router which forwards all messages to a command bus or event bus depending on the
        //Prooph\ServiceBus\Message\MessageHeader::TYPE
        $bernardRouter = new BernardRouter(new CommandBus(), $consumerEventBus);

        $bernardConsumer = new Consumer($bernardRouter, new MiddlewareBuilder());

        //We use the same queue name here as we've defined for the message dispatcher above
        $bernardConsumer->tick($this->persistentFactory->create('test-queue'));

        $this->assertNotNull($somethingDoneListener->getLastMessage());

        $this->assertEquals($event->payload(), $somethingDoneListener->getLastMessage()->payload());
    }

    /**
     * @test
     * @expectedException \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function it_throws_runtime_exception_if_a_deferred_is_passed_to_invoke()
    {
        $event = new SomethingDone(['data' => 'test event']);

        //The message dispatcher works with a ready-to-use bernard producer and one queue
        $messageProducer = new BernardMessageProducer($this->bernardProducer, 'test-queue');

        $deferred = $this->prophesize(Deferred::class);

        $messageProducer($event, $deferred->reveal());
    }
}
