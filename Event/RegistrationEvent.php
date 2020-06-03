<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\CommerceBundle\Model\Registration;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AccountEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegistrationEvent extends Event
{
    const REGISTRATION_INITIALIZE = 'ekyna_commerce.account.registration.initialize';
    const REGISTRATION_SUCCESS    = 'ekyna_commerce.account.registration.success';
    const REGISTRATION_COMPLETED  = 'ekyna_commerce.account.registration.completed';


    /**
     * @var Registration
     */
    private $registration;

    /**
     * @var Response
     */
    private $response;


    /**
     * Constructor.
     *
     * @param Registration $registration
     * @param Request      $request
     */
    public function __construct(Registration $registration)
    {
        $this->registration = $registration;
    }

    /**
     * Returns the registration.
     *
     * @return Registration
     */
    public function getRegistration(): Registration
    {
        return $this->registration;
    }

    /**
     * Returns the response.
     *
     * @return Response|null
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Sets the response.
     *
     * @param Response $response
     *
     * @return RegistrationEvent
     */
    public function setResponse(Response $response = null): RegistrationEvent
    {
        $this->response = $response;

        return $this;
    }
}
