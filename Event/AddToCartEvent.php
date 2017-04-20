<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Cart\Model\CartItemInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class AddToCartEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddToCartEvent extends Event
{
    public const INITIALIZE = 'ekyna_commerce.add_to_cart.initialize';
    public const SUCCESS    = 'ekyna_commerce.add_to_cart.success';
    public const FAILURE    = 'ekyna_commerce.add_to_cart.failure';


    private SubjectInterface   $subject;
    private ?Modal             $modal;
    private ?CartItemInterface $item;

    private ?string   $message  = null;
    private ?Response $response = null;


    public function __construct(SubjectInterface $subject, ?Modal $modal, ?CartItemInterface $item)
    {
        $this->subject = $subject;
        $this->modal = $modal;
        $this->item = $item;
    }

    public function getSubject(): SubjectInterface
    {
        return $this->subject;
    }

    public function setModal(?Modal $modal): AddToCartEvent
    {
        $this->modal = $modal;

        return $this;
    }

    public function getModal(): ?Modal
    {
        return $this->modal;
    }

    public function setItem(?CartItemInterface $item): AddToCartEvent
    {
        $this->item = $item;

        return $this;
    }

    public function getItem(): ?CartItemInterface
    {
        return $this->item;
    }

    public function setMessage(?string $message): AddToCartEvent
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setResponse(?Response $response): AddToCartEvent
    {
        $this->response = $response;

        return $this;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}
