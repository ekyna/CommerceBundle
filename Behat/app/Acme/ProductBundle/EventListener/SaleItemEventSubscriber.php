<?php

namespace Acme\ProductBundle\EventListener;

use Acme\ProductBundle\Entity\Product;
use Ekyna\Bundle\CommerceBundle\Event\SaleItemFormEvent;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvent;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvents;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

/**
 * Class SaleItemEventSubscriber
 * @package Acme\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var SubjectHelperInterface
     */
    private $subjectHelper;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface $subjectHelper
     */
    public function __construct(SubjectHelperInterface $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    /**
     * Sale item build event handler.
     *
     * @param SaleItemEvent $event
     */
    public function onSaleItemBuild(SaleItemEvent $event)
    {
        if (null === $product = $this->getProductFromEvent($event)) {
            return;
        }

        $item = $event->getItem();

        $this->subjectHelper->assign($item, $product);

        $item
            ->setDesignation($product->getDesignation())
            ->setReference($product->getReference())
            ->setNetPrice($product->getNetPrice())
            ->setWeight($product->getWeight())
            ->setTaxGroup($product->getTaxGroup());
    }

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
     * Returns the product from the given event.
     *
     * @param SaleItemEvent $event
     *
     * @return null|Product
     */
    private function getProductFromEvent(SaleItemEvent $event)
    {
        $item = $event->getItem();

        $product = $this->subjectHelper->resolve($item, false);
        if ($product instanceof Product) {
            return $product;
        }

        return null;
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
