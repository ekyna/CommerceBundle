<?php

namespace Acme\ProductBundle\EventListener;

use Acme\Product\EventListener\SaleItemEventSubscriber as BaseSubscriber;
use Ekyna\Bundle\CommerceBundle\Event\SaleItemFormEvent;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

/**
 * Class SaleItemEventSubscriber
 * @package Acme\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemEventSubscriber extends BaseSubscriber implements EventSubscriberInterface
{
    /**
     * Sale item build form event handler.
     *
     * @param SaleItemFormEvent $event
     */
    public function onSaleItemBuildForm(SaleItemFormEvent $event)
    {
        if (null === $this->getProductFromEvent($event)) {
            return;
        }

        $form = $event->getForm();

        // Quantity
        $form->add('quantity', IntegerType::class, [
            'label' => 'ekyna_core.field.quantity',
            'attr'  => [
                'min' => 1,
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SaleItemEvents::INITIALIZE    => ['onSaleItemBuild'],
            SaleItemEvents::BUILD         => ['onSaleItemBuild'],
            //SaleItemEvents::ADJUSTMENTS   => ['onSaleItemAdjustments'],
            SaleItemFormEvent::BUILD_FORM => ['onSaleItemBuildForm'],
        ];
    }
}
