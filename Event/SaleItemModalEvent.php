<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class SaleItemModalEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemModalEvent extends Event
{
    const MODAL = 'ekyna_commerce.sale_item.modal';

    const EVENT_ADD       = 'ekyna_commerce.sale_item.add';
    const EVENT_CONFIGURE = 'ekyna_commerce.sale_item.configure';

    /**
     * @var Modal
     */
    private $modal;

    /**
     * @var SaleItemInterface
     */
    private $item;


    /**
     * Constructor.
     *
     * @param Modal             $modal
     * @param SaleItemInterface $item
     */
    public function __construct(Modal $modal, SaleItemInterface $item)
    {
        $this->modal = $modal;
        $this->item = $item;
    }

    /**
     * Returns the modal.
     *
     * @return Modal
     */
    public function getModal()
    {
        return $this->modal;
    }

    /**
     * Returns the item.
     *
     * @return SaleItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }
}