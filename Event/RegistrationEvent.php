<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\CommerceBundle\Model\Registration;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AccountEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegistrationEvent extends Event
{
    const REGISTRATION_SUCCESS = 'ekyna_commerce.account.registration.success';
    const REGISTRATION_COMPLETED = 'ekyna_commerce.account.registration.completed';


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
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * Returns the response.
     *
     * @return Response
     */
    public function getResponse()
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
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }
}
