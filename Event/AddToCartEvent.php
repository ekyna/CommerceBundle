<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AddToCartEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddToCartEvent extends Event
{
    const INITIALIZE = 'ekyna_commerce.add_to_cart.initialize';
    const SUCCESS    = 'ekyna_commerce.add_to_cart.success';
    const FAILURE    = 'ekyna_commerce.add_to_cart.failure';

    /**
     * @var SubjectInterface
     */
    private $subject;

    /**
     * @var Modal
     */
    private $modal;

    /**
     * @var string
     */
    private $message;

    /**
     * @var Response
     */
    private $response;


    /**
     * Constructor.
     *
     * @param SubjectInterface $subject
     * @param Modal            $modal
     */
    public function __construct(SubjectInterface $subject, Modal $modal = null)
    {
        $this->subject = $subject;
        $this->modal = $modal;
    }

    /**
     * Returns the subject.
     *
     * @return SubjectInterface
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Returns the modal.
     *
     * @return Modal|null
     */
    public function getModal()
    {
        return $this->modal;
    }

    /**
     * Sets the modal.
     *
     * @param Modal $modal
     *
     * @return AddToCartEvent
     */
    public function setModal(Modal $modal = null)
    {
        $this->modal = $modal;

        return $this;
    }

    /**
     * Returns the message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the message.
     *
     * @param string $message
     *
     * @return AddToCartEvent
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
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
     * @return AddToCartEvent
     */
    public function setResponse(Response $response = null)
    {
        $this->response = $response;

        return $this;
    }
}