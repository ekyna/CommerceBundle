<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
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
 * Class ShipmentMethodPickType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodPickType extends AbstractType
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
     * @var ContextProviderInterface
     */
    private $contextProvider;

    /**
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;

    /**
     * @var FormatterFactory
     */
    private $formatterFactory;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface[]
     */
    private $availableMethods;

    /**
     * @var \Ekyna\Component\Commerce\Shipment\Model\ResolvedShipmentPrice[]
     */
    private $availablePrices;


    /**
     * Constructor.
     *
     * @param ShipmentPriceResolverInterface    $priceResolver
     * @param RegistryInterface                 $gatewayRegistry
     * @param ShipmentMethodRepositoryInterface $methodRepository
     * @param ContextProviderInterface          $contextProvider
     * @param CurrencyConverterInterface        $currencyConverter
     * @param FormatterFactory                  $formatterFactory
     * @param TranslatorInterface               $translator
     */
    public function __construct(
        ShipmentPriceResolverInterface $priceResolver,
        RegistryInterface $gatewayRegistry,
        ShipmentMethodRepositoryInterface $methodRepository,
        ContextProviderInterface $contextProvider,
        CurrencyConverterInterface $currencyConverter,
        FormatterFactory $formatterFactory,
        TranslatorInterface $translator
    ) {
        $this->priceResolver = $priceResolver;
        $this->gatewayRegistry = $gatewayRegistry;
        $this->methodRepository = $methodRepository;
        $this->contextProvider = $contextProvider;
        $this->currencyConverter = $currencyConverter;
        $this->formatterFactory = $formatterFactory;
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
        $this->context = $this->contextProvider->getContext($sale);

        if (null !== $sale) {
            $this->availablePrices = $this->priceResolver->getAvailablePricesBySale($sale, $availableOnly);

            if ($withPrice) {
                $methods = [];

                foreach ($this->availablePrices as $price) {
                    $methods[] = $price->getMethod();
                }

                return $this->availableMethods = $this->filterMethods($methods, $return);
            }
        }

        $criteria = $availableOnly ? ['available' => true, 'enabled' => true] : ['enabled' => true];

        return $this->availableMethods = $this->filterMethods(
            (array)$this->methodRepository->findBy($criteria, ['position' => 'ASC']),
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

                return $gateway->supports(GatewayInterface::CAPABILITY_RETURN);
            });
        }

        return array_filter($methods, function (ShipmentMethodInterface $method) {
            $gateway = $this->gatewayRegistry->getGateway($method->getGatewayName());

            return $gateway->supports(GatewayInterface::CAPABILITY_SHIPMENT);
        });
    }

    /**
     * Finds the price by method.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ResolvedShipmentPrice|null
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
            'data-relay'    => $gateway->supports(GatewayInterface::CAPABILITY_RELAY) ? 1 : 0,
            'data-parcel'   => $gateway->supports(GatewayInterface::CAPABILITY_PARCEL) ? 1 : 0,
            'data-mobile'   => $gateway->requires(GatewayInterface::REQUIREMENT_MOBILE) ? 1 : 0,
        ];

        if (null !== $price = $this->findPriceByMethod($method)) {
            $attr['data-price'] = $price->getPrice();
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
        if (null !== $price = $this->findPriceByMethod($method)) {
            if ($price->isFree()) {
                $amount = $this->translator->trans('ekyna_commerce.checkout.shipment.free_shipping');
            } else {
                $amount = $net = $price->getPrice();
                if ($this->context->isAtiDisplayMode()) {
                    $suffix = $this->translator->trans('ekyna_commerce.pricing.vat_display_mode.ati');
                    foreach ($price->getTaxes() as $rate) {
                        $amount += $net * $rate / 100;
                    }
                } else {
                    $suffix = $this->translator->trans('ekyna_commerce.pricing.vat_display_mode.net');
                }

                $currency = $this->context->getCurrency()->getCode();

                $amount = $this->currencyConverter->convert(
                    $amount,
                    $this->currencyConverter->getDefaultCurrency(),
                    $this->context->getCurrency()->getCode(),
                    $this->context->getDate()
                );

                $amount = $this->formatterFactory->createFromContext($this->context)->currency($amount, $currency);

                $amount = sprintf('%s&nbsp;%s', $amount, $suffix);
            }

            return sprintf('%s (%s)', $method->getTitle(), $amount);
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
            return $this->buildChoices($options['sale'], $options['return'], $options['with_price'],
                $options['available']);
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
