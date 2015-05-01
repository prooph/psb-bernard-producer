<?php
/*
 * This file is part of the prooph/psb-bernard-dispatcher.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 31.10.14 - 17:52
 */
namespace Prooph\ServiceBusTest;

use Bernard\Consumer;
use Bernard\Doctrine\MessagesSchema;
use Bernard\Driver\DoctrineDriver;
use Bernard\Middleware\MiddlewareBuilder;
use Bernard\Producer;
use Bernard\QueueFactory\PersistentFactory;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\InvokeStrategy\HandleCommandStrategy;
use Prooph\ServiceBus\Message\Bernard\BernardRouter;
use Prooph\ServiceBus\Message\Bernard\BernardSerializer;
use Prooph\ServiceBus\Message\Bernard\MessageDispatcher;
use Prooph\ServiceBus\Message\FromRemoteMessageTranslator;
use Prooph\ServiceBus\Message\ProophDomainMessageToRemoteMessageTranslator;
use Prooph\ServiceBus\Message\ToRemoteMessageTranslator;
use Prooph\ServiceBus\Router\CommandRouter;
use Prooph\ServiceBus\Router\EventRouter;
use Prooph\ServiceBusTest\Mock\DoSomething;
use Prooph\ServiceBusTest\Mock\DoSomethingHandler;
use Prooph\ServiceBusTest\Mock\DoSomethingInvokeStrategy;
use Prooph\ServiceBusTest\Mock\SomethingDone;
use Prooph\ServiceBusTest\Mock\SomethingDoneInvokeStrategy;
use Prooph\ServiceBusTest\Mock\SomethingDoneListener;

/**
 * Class BernardMessageDispatcherTest
 *
 * @package Prooph\ServiceBusTest
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class BernardMessageDispatcherTest extends TestCase
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
        $connection = DriverManager::getConnection(array(
            'driver' => 'pdo_sqlite',
            'dbname' => ':memory:'
        ));

        $schema = new Schema();

        MessagesSchema::create($schema);

        array_map(array($connection, "executeQuery"), $schema->toSql($connection->getDatabasePlatform()));

        $doctrineDriver = new DoctrineDriver($connection);

        //Use the serializer provided by psb-bernard-dispatcher
        $this->persistentFactory = new PersistentFactory($doctrineDriver, new BernardSerializer());

        $this->bernardProducer = new Producer($this->persistentFactory, new MiddlewareBuilder());

        $this->toRemoteMessageTranslator = new ProophDomainMessageToRemoteMessageTranslator();
    }

    /**
     * @test
     */
    public function it_sends_a_command_to_queue_pulls_it_with_consumer_and_forwards_it_to_command_bus()
    {
        $command = DoSomething::fromData("test command");

        //The message dispatcher works with a ready-to-use bernard producer and one queue
        $messageDispatcher = new MessageDispatcher($this->bernardProducer, 'test-queue');

        //Normally you would send the command on a command bus. We skip this step here cause we are only
        //interested in the function of the message dispatcher
        $messageDispatcher->dispatch($command->toRemoteMessage());

        //Set up command bus which will receive the command message from the bernard consumer
        $consumerCommandBus = new CommandBus();

        $consumerCommandBus->utilize(new FromRemoteMessageTranslator());

        $doSomethingHandler = new DoSomethingHandler();

        $consumerCommandBus->utilize(new CommandRouter([
            $command->messageName() => $doSomethingHandler
        ]));

        $consumerCommandBus->utilize(new DoSomethingInvokeStrategy());

        //We use a special bernard router which forwards all messages to a command bus or event bus depending on the
        //Prooph\ServiceBus\Message\MessageHeader::TYPE
        $bernardRouter = new BernardRouter($consumerCommandBus, new EventBus());

        $bernardConsumer = new Consumer($bernardRouter, new MiddlewareBuilder());

        //We use the same queue name here as we've defined for the message dispatcher above
        $bernardConsumer->tick($this->persistentFactory->create('test-queue'));

        $this->assertNotNull($doSomethingHandler->lastCommand());

        $this->assertEquals($command->payload(), $doSomethingHandler->lastCommand()->payload());
    }

    /**
     * @test
     */
    public function it_sends_a_event_to_queue_pulls_it_with_consumer_and_forwards_it_to_event_bus()
    {
        $event = SomethingDone::fromData("test event");

        //The message dispatcher works with a ready-to-use bernard producer and one queue
        $messageDispatcher = new MessageDispatcher($this->bernardProducer, 'test-queue');

        //Normally you would send the event on a event bus. We skip this step here cause we are only
        //interested in the function of the message dispatcher
        $messageDispatcher->dispatch($event->toRemoteMessage());

        //Set up event bus which will receive the event message from the bernard consumer
        $consumerEventBus = new EventBus();

        $consumerEventBus->utilize(new FromRemoteMessageTranslator());

        $somethingDoneListener = new SomethingDoneListener();

        $consumerEventBus->utilize(new EventRouter([
            $event->messageName() => [$somethingDoneListener]
        ]));

        $consumerEventBus->utilize(new SomethingDoneInvokeStrategy());

        //We use a special bernard router which forwards all messages to a command bus or event bus depending on the
        //Prooph\ServiceBus\Message\MessageHeader::TYPE
        $bernardRouter = new BernardRouter(new CommandBus(), $consumerEventBus);

        $bernardConsumer = new Consumer($bernardRouter, new MiddlewareBuilder());

        //We use the same queue name here as we've defined for the message dispatcher above
        $bernardConsumer->tick($this->persistentFactory->create('test-queue'));

        $this->assertNotNull($somethingDoneListener->lastEvent());

        $this->assertEquals($event->payload(), $somethingDoneListener->lastEvent()->payload());
    }
}
 