<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service;

use Ekyna\Bundle\CommerceBundle\Event\SaleItemFormEvent;
use Ekyna\Component\Commerce\Common\Helper\SaleItemHelper as BaseHelper;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class SaleItemHelper
 * @package Ekyna\Bundle\CommerceBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemHelper extends BaseHelper
{
    /**
     * Builds the sale item subject form
     */
    public function buildForm(SaleItemInterface $item, FormInterface $form): SaleItemFormEvent
    {
        $this->assertAssignedSubject($item);

        $event = new SaleItemFormEvent($item, $form, null);

        $this->eventDispatcher->dispatch($event, SaleItemFormEvent::BUILD_FORM);

        return $event;
    }

    /**
     * Builds the sale item subject form view.
     */
    public function buildFormView(SaleItemInterface $item, FormInterface $form, FormView $view): SaleItemFormEvent
    {
        $this->assertAssignedSubject($item);

        $event = new SaleItemFormEvent($item, $form, $view);

        $this->eventDispatcher->dispatch($event, SaleItemFormEvent::BUILD_VIEW);

        return $event;
    }
}
