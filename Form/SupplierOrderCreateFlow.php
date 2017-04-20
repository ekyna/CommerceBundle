<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form;

use Craue\FormFlowBundle\Event\PostBindRequestEvent;
use Craue\FormFlowBundle\Event\PostBindSavedDataEvent;
use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowEvents;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierOrderType;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Updater\SupplierOrderUpdaterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SupplierOrderCreateFlow
 * @package Ekyna\Bundle\CommerceBundle\Form
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderCreateFlow extends FormFlow implements EventSubscriberInterface
{
    private SupplierOrderUpdaterInterface $updater;

    public function __construct(SupplierOrderUpdaterInterface $updater)
    {
        $this->updater = $updater;
    }

    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void
    {
        parent::setEventDispatcher($dispatcher);

        $dispatcher->addSubscriber($this);
    }

    protected function loadStepsConfig(): array
    {
        return [
            [
                'label'     => 'supplier',
                'form_type' => SupplierOrderType::class,
                'skip'      => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    /** @var SupplierOrderInterface $supplierOrder */
                    $supplierOrder = $flow->getFormData();

                    return $estimatedCurrentStepNumber == 1 && null !== $supplierOrder->getSupplier();
                },
            ],
            [
                'label'        => 'configuration',
                'form_type'    => SupplierOrderType::class,
                'form_options' => [
                    'validation_groups' => ['Default'],
                ],
            ],
        ];
    }

    /**
     * Post bind request event handler.
     */
    public function onPostBindRequest(PostBindRequestEvent $event): void
    {
        $order = $event->getFormData();

        $this->update($order);
    }

    /**
     * Post bind saved data event handler.
     */
    public function onPostBindSavedData(PostBindSavedDataEvent $event): void
    {
        $order = $event->getFormData();

        $this->update($order);
    }

    private function update(SupplierOrderInterface $order): void
    {
        $this->updater->updateCurrency($order);
        $this->updater->updateCarrier($order);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormFlowEvents::POST_BIND_REQUEST    => 'onPostBindRequest',
            FormFlowEvents::POST_BIND_SAVED_DATA => 'onPostBindSavedData',
        ];
    }
}
