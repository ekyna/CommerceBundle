<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\RegistryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentMethodRepositoryInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ShipmentMethodChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodChoiceType extends AbstractType
{
    /**
     * @var ShipmentPriceResolverInterface
     */
    private $priceResolver;

    /**
     * @var RegistryInterface
     */
    private $gatewayRegistry;

    /**
     * @var ShipmentMethodRepositoryInterface
     */
    private $methodRepository;

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface[]
     */
    private $availableMethods;

    /**
     * @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface[]
     */
    private $availablePrices;

    /**
     * @var bool
     */
    private $freeShipping = false;


    /**
     * Constructor.
     *
     * @param ShipmentPriceResolverInterface    $priceResolver
     * @param RegistryInterface                 $gatewayRegistry
     * @param ShipmentMethodRepositoryInterface $methodRepository
     * @param Formatter                         $formatter
     * @param TranslatorInterface               $translator
     */
    public function __construct(
        ShipmentPriceResolverInterface $priceResolver,
        RegistryInterface $gatewayRegistry,
        ShipmentMethodRepositoryInterface $methodRepository,
        Formatter $formatter,
        TranslatorInterface $translator
    ) {
        $this->priceResolver = $priceResolver;
        $this->gatewayRegistry = $gatewayRegistry;
        $this->methodRepository = $methodRepository;
        $this->formatter = $formatter;
        $this->translator = $translator;
    }

    /**
     * Builds the choices.
     *
     * @param SaleInterface|null $sale
     * @param bool               $return
     * @param bool               $withPrice
     * @param bool               $availableOnly
     *
     * @return array|\Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface[]
     */
    private function buildChoices(SaleInterface $sale = null, $return = false, $withPrice = true, $availableOnly = true)
    {
        $this->availableMethods = [];
        $this->availablePrices = [];

        if (null !== $sale) {
            $this->freeShipping = $this->priceResolver->hasFreeShipping($sale);

            $this->availablePrices = $this->priceResolver->getAvailablePricesBySale($sale, $availableOnly);

            if ($withPrice) {
                $methods = [];

                foreach ($this->availablePrices as $price) {
                    $methods[] = $price->getMethod();
                }

                return $this->availableMethods = $this->filterMethods($methods, $return);
            }
        }

        $sorting = ['position' => 'ASC'];
        $criteria = $availableOnly ? ['available' => true, 'enabled' => true] : ['enabled' => true];

        return $this->availableMethods = $this->filterMethods(
            (array)$this->methodRepository->findBy($criteria, $sorting),
            $return
        );
    }

    /**
     * Filters the methods regarding to their capabilities (shipment/return).
     *
     * @param array $methods
     * @param bool  $return
     *
     * @return array
     */
    private function filterMethods(array $methods, bool $return)
    {
        if ($return) {
            return array_filter($methods, function (ShipmentMethodInterface $method) {
                $gateway = $this->gatewayRegistry->getGateway($method->getGatewayName());

                return $gateway->support(GatewayInterface::CAPABILITY_RETURN);
            });
        }

        return array_filter($methods, function (ShipmentMethodInterface $method) {
            $gateway = $this->gatewayRegistry->getGateway($method->getGatewayName());

            return $gateway->support(GatewayInterface::CAPABILITY_SHIPMENT);
        });
    }

    /**
     * Finds the price by method.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface|null
     */
    private function findPriceByMethod(ShipmentMethodInterface $method)
    {
        if (null !== $this->availablePrices) {
            foreach ($this->availablePrices as $price) {
                if ($price->getMethod() === $method) {
                    return $price;
                }
            }
        }

        return null;
    }

    /**
     * Builds the choice attributes.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return array
     */
    public function buildChoiceAttr(ShipmentMethodInterface $method)
    {
        $gateway = $this->gatewayRegistry->getGateway($method->getGatewayName());

        $attr = [
            'data-platform' => $gateway->getPlatform()->getName(),
            'data-gateway'  => $gateway->getName(),
            'data-relay'    => $gateway->support(GatewayInterface::CAPABILITY_RELAY) ? 1 : 0,
            'data-parcel'   => $gateway->support(GatewayInterface::CAPABILITY_PARCEL) ? 1 : 0,
        ];

        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface $price */
        if (null !== $price = $this->findPriceByMethod($method)) {
            $attr['data-price'] = $price->getNetPrice();
        }

        return $attr;
    }

    /**
     * Builds the choice label.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return string
     */
    public function buildChoiceLabel(ShipmentMethodInterface $method)
    {
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface $price */
        if (null !== $price = $this->findPriceByMethod($method)) {
            $price = $this->freeShipping
                ? $this->translator->trans('ekyna_commerce.checkout.shipment.free_shipping')
                : $this->formatter->currency($price->getNetPrice());

            return sprintf('%s (%s)', $method->getTitle(), $price);
        }

        return $method->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var SaleInterface $sale */
        if (null !== $sale = $options['sale']) {
            $view->vars['country'] = $sale->getDeliveryCountry();
        }
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'shipment-method');
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $choices = function (Options $options) {
            return $this->buildChoices($options['sale'], $options['return'], $options['with_price'], $options['available']);
        };

        $resolver
            ->setDefaults([
                'label'        => 'ekyna_commerce.shipment_method.label.singular',
                'sale'         => null,
                'with_price'   => true,
                'return'       => false,
                'available'    => true,
                'choices'      => $choices,
                'choice_value' => 'id',
                'choice_attr'  => [$this, 'buildChoiceAttr'],
                'choice_label' => [$this, 'buildChoiceLabel'],
            ])
            ->setAllowedTypes('sale', ['null', SaleInterface::class])
            ->setAllowedTypes('with_price', 'bool')
            ->setAllowedTypes('return', 'bool')
            ->setAllowedTypes('available', 'bool');
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_shipment_method_choice';
    }
}
