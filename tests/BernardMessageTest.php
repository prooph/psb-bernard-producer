<?php
/*
 * This file is part of the prooph/psb-bernard-producer.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 9/1/15 - 8:51 AM
 */
namespace ProophTest\ServiceBus;

use Prooph\Common\Messaging\Message;
use Prooph\ServiceBus\Message\Bernard\BernardMessage;
use PHPUnit\Framework\TestCase;

/**
 * Class BernardMessageTest
 *
 * @package Prooph\ServiceBusTest
 */
final class BernardMessageTest extends TestCase
{
    /**
     * @test
     */
    public function it_wraps_a_prooph_message()
    {
        $proophMessage = $this->prophesize(Message::class);

        $proophMessage->messageName()->willReturn('prooph-message');

        $bernardMessage = BernardMessage::fromProophMessage($proophMessage->reveal());

        $this->assertEquals('prooph-message', $bernardMessage->getName());
        $this->assertSame($proophMessage->reveal(), $bernardMessage->getProophMessage());
    }
}
