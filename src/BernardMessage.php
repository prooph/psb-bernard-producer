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

use Bernard\Message;
use Prooph\Common\Messaging\Message as ProophMessage;

/**
 * Class BernardMessage
 *
 * @package Prooph\ServiceBus\Message\Bernard
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class BernardMessage implements Message
{
    /**
     * @var ProophMessage
     */
    private $proophMessage;

    public static function fromProophMessage(ProophMessage $remoteMessage)
    {
        $instance = new self();
        $instance->proophMessage = $remoteMessage;
        return $instance;
    }
    /**
     * @return string
     */
    public function getName()
    {
        return $this->proophMessage->messageName();
    }

    /**
     * @return ProophMessage
     */
    public function getProophMessage()
    {
        return $this->proophMessage;
    }
}
