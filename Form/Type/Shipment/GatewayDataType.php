<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Shipment\Gateway\RegistryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class GatewayDataType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GatewayDataType extends AbstractType
{
    /**
     * @var RegistryInterface
     */
    private $registry;


    /**
     * Constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            $shipment = $form->getParent()->getData();
            if (!$shipment instanceof ShipmentInterface) {
                throw new RuntimeException("Expected instance of " . ShipmentInterface::class);
            }

            if (null === $method = $shipment->getMethod()) {
                return;
            }

            $this->registry->getGateway($method->getGatewayName())->buildForm($form);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            if (null === $method = $form->getParent()->get('method')->getData()) {
                return;
            }

            $this->registry->getGateway($method->getGatewayName())->buildForm($form);
        });
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $shipment = $form->getParent()->getData();
        if (!$shipment instanceof ShipmentInterface) {
            throw new RuntimeException("Expected instance of " . ShipmentInterface::class);
        }
        if (null === $sale = $shipment->getSale()) {
            throw new RuntimeException("Shipment's sale must be set at this point.");
        }

        $view->vars['attr'] = array_replace($view->vars['attr'], [
            'data-order'    => $sale->getId(),
            'data-shipment' => $shipment->getId(),
            'data-method'   => $options['method-selector'],
            'data-return'   => $shipment->isReturn() ? 1 : 0,
        ]);

        FormUtil::addClass($view, 'commerce-shipment-gateway-data');
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'           => 'ekyna_core.field.config',
            'method-selector' => '.shipment-method',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_shipment_gateway_data';
    }
}