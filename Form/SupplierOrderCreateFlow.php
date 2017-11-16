<?php

namespace Ekyna\Bundle\CommerceBundle\Form;

use Craue\FormFlowBundle\Event\PostBindRequestEvent;
use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowEvents;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierOrderType;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SupplierOrderCreateFlow
 * @package Ekyna\Bundle\CommerceBundle\Form
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderCreateFlow extends FormFlow implements EventSubscriberInterface
{
    /**
     * @var ResourceOperatorInterface
     */
    private $operator;


    /**
     * Constructor.
     *
     * @param ResourceOperatorInterface $operator
     */
    public function __construct(ResourceOperatorInterface $operator)
    {
        $this->operator = $operator;
    }

    /**
     * @inheritdoc
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        parent::setEventDispatcher($dispatcher);

        $dispatcher->addSubscriber($this);
    }

    /**
     * @inheritdoc
     */
    protected function loadStepsConfig()
    {
        return [
            [
                'label'        => 'supplier',
                'form_type'    => SupplierOrderType::class,
                'skip'         => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface $supplierOrder */
                    $supplierOrder = $flow->getFormData();

                    return $estimatedCurrentStepNumber == 1 && null !== $supplierOrder->getSupplier();
                },
            ],
            [
                'label'     => 'configuration',
                'form_type' => SupplierOrderType::class,
                'form_options' => [
                    'validation_groups' => ['Default'],
                ],
            ],
        ];
    }

    /**
     * Post bind request event handler.
     *
     * @param PostBindRequestEvent $event
     */
    public function onPostBindRequest(PostBindRequestEvent $event)
    {
        $order = $event->getFormData();

        $this->operator->initialize($order);
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormFlowEvents::POST_BIND_REQUEST => 'onPostBindRequest',
        );
    }
}
