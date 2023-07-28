<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ResolvedShipmentPrice;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentMethodRepositoryInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;
use Ekyna\Component\Commerce\Shipment\ShipmentUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_filter;
use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class ShipmentMethodPickType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodPickType extends AbstractType
{
    private ShipmentPriceResolverInterface    $priceResolver;
    private GatewayRegistryInterface          $gatewayRegistry;
    private ShipmentMethodRepositoryInterface $methodRepository;
    private ContextProviderInterface          $contextProvider;
    private CurrencyConverterInterface        $currencyConverter;
    private FormatterFactory                  $formatterFactory;
    private TranslatorInterface               $translator;
    private ContextInterface                  $context;

    /** @var ResolvedShipmentPrice[] */
    private array $availablePrices;


    public function __construct(
        ShipmentPriceResolverInterface    $priceResolver,
        GatewayRegistryInterface          $gatewayRegistry,
        ShipmentMethodRepositoryInterface $methodRepository,
        ContextProviderInterface          $contextProvider,
        CurrencyConverterInterface        $currencyConverter,
        FormatterFactory                  $formatterFactory,
        TranslatorInterface               $translator
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
     * @return array<ShipmentMethodInterface>
     */
    private function buildChoices(
        ShipmentInterface|SaleInterface $subject = null,
        bool                            $withPrice = true,
        bool                            $availableOnly = true
    ): array {
        $this->availablePrices = [];

        $return = false;
        if ($subject instanceof ShipmentInterface) {
            $shipment = $subject;
            $sale = $shipment->getSale();
            $return = $shipment->isReturn();
            $virtual = !ShipmentUtil::hasPhysicalItem($shipment);
        } else {
            $sale = $subject;
            $virtual = !$sale->hasPhysicalItem();
        }

        $this->context = $this->contextProvider->getContext($sale);

        if (null !== $sale) {
            $this->availablePrices = $this->priceResolver->getAvailablePricesBySale($sale, $availableOnly);

            if ($withPrice) {
                $methods = [];

                foreach ($this->availablePrices as $price) {
                    $methods[] = $price->getMethod();
                }

                return $this->filterMethods($methods, $return, $virtual);
            }
        }

        $criteria = $availableOnly ? ['available' => true, 'enabled' => true] : ['enabled' => true];

        return $this->filterMethods(
            (array)$this->methodRepository->findBy($criteria, ['position' => 'ASC']),
            $return, $virtual
        );
    }

    /**
     * Filters the methods regarding their capabilities (shipment/return).
     */
    private function filterMethods(array $methods, bool $return, bool $virtual): array
    {
        $methods = array_filter($methods, function(ShipmentMethodInterface $method) use ($virtual) {
            $gateway = $this->gatewayRegistry->getGateway($method->getGatewayName());

            return $virtual xor !$gateway->supports(GatewayInterface::CAPABILITY_VIRTUAL);
        });

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
     */
    private function findPriceByMethod(ShipmentMethodInterface $method): ?ResolvedShipmentPrice
    {
        if (!empty($this->availablePrices)) {
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
     */
    public function buildChoiceAttr(ShipmentMethodInterface $method): array
    {
        $gateway = $this->gatewayRegistry->getGateway($method->getGatewayName());

        $attr = [
            'data-platform' => $gateway->getPlatform()->getName(),
            'data-gateway'  => $gateway->getName(),
            'data-relay'    => $gateway->supports(GatewayInterface::CAPABILITY_RELAY) ? 1 : 0,
            'data-parcel'   => $gateway->supports(GatewayInterface::CAPABILITY_PARCEL) ? 1 : 0,
            'data-virtual'  => $gateway->requires(GatewayInterface::CAPABILITY_VIRTUAL) ? 1 : 0,
            'data-mobile'   => $gateway->requires(GatewayInterface::REQUIREMENT_MOBILE) ? 1 : 0,
        ];

        if (null !== $price = $this->findPriceByMethod($method)) {
            $attr['data-price'] = $price->getPrice();
        }

        return $attr;
    }

    /**
     * Builds the choice label.
     */
    public function buildChoiceLabel(ShipmentMethodInterface $method): string
    {
        if (null !== $price = $this->findPriceByMethod($method)) {
            if ($price->isFree()) {
                $amount = $this->translator->trans('checkout.shipment.free_shipping', [], 'EkynaCommerce');
            } else {
                $amount = $net = $price->getPrice();
                if ($this->context->isAtiDisplayMode()) {
                    $suffix = $this->translator->trans('pricing.vat_display_mode.ati', [], 'EkynaCommerce');
                    foreach ($price->getTaxes() as $rate) {
                        $amount += $net * $rate / 100;
                    }
                } else {
                    $suffix = $this->translator->trans('pricing.vat_display_mode.net', [], 'EkynaCommerce');
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

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (null === $subject = $options['subject']) {
            return;
        }

        $sale = $subject instanceof ShipmentInterface ? $subject->getSale() : $subject;

        $view->vars['country'] = $sale->getDeliveryCountry();
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'shipment-method');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $choices = function (Options $options) {
            return $this->buildChoices(
                $options['subject'],
                $options['with_price'],
                $options['available']
            );
        };

        $resolver
            ->setDefaults([
                'label'                     => t('shipment_method.label.singular', [], 'EkynaCommerce'),
                'choice_translation_domain' => false,
                'subject'                   => null,
                'with_price'                => true,
                'available'                 => true,
                'choices'                   => $choices,
                'choice_value'              => 'id',
                'choice_attr'               => [$this, 'buildChoiceAttr'],
                'choice_label'              => [$this, 'buildChoiceLabel'],
            ])
            ->setAllowedTypes('subject', ['null', ShipmentInterface::class, SaleInterface::class])
            ->setAllowedTypes('with_price', 'bool')
            ->setAllowedTypes('available', 'bool');
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_shipment_method_choice';
    }
}
