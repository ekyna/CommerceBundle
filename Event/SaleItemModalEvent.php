<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class SaleItemModalEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemModalEvent extends Event
{
    public const MODAL           = 'ekyna_commerce.sale_item.modal';
    public const EVENT_ADD       = 'ekyna_commerce.sale_item.add';
    public const EVENT_CONFIGURE = 'ekyna_commerce.sale_item.configure';

    private Modal             $modal;
    private SaleItemInterface $item;


    public function __construct(Modal $modal, SaleItemInterface $item)
    {
        $this->modal = $modal;
        $this->item = $item;
    }

    public function getModal(): Modal
    {
        return $this->modal;
    }

    public function getItem(): SaleItemInterface
    {
        return $this->item;
    }
}
