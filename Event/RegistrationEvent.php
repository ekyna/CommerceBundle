<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\CommerceBundle\Model\Registration;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class AccountEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegistrationEvent extends Event
{
    public const REGISTRATION_INITIALIZE = 'ekyna_commerce.account.registration.initialize';
    public const REGISTRATION_SUCCESS    = 'ekyna_commerce.account.registration.success';
    public const REGISTRATION_COMPLETED  = 'ekyna_commerce.account.registration.completed';


    private Registration $registration;
    private ?Response    $response = null;


    public function __construct(Registration $registration)
    {
        $this->registration = $registration;
    }

    public function getRegistration(): Registration
    {
        return $this->registration;
    }

    public function setResponse(?Response $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}
