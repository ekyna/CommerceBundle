<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Notification;

use Ekyna\Bundle\CommerceBundle\Model\Notification;

/**
 * Class NotificationMailer
 * @package Ekyna\Bundle\CommerceBundle\Service\Notification
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotificationMailer
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var
     */
    private $renderer;


    /**
     * Constructor.
     *
     * @param \Swift_Mailer $mailer
     */
    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function notify(Notification $notification)
    {
        $message = \Swift_Message::newInstance();

        //$message->setCc
    }
}
