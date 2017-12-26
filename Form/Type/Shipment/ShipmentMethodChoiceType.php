<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentMethodRepositoryInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * @var ShipmentMethodRepositoryInterface
     */
    private $methodRepository;

    /**
     * @var array|\Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface[]
     */
    private $availableMethods;

    /**
     * @var array|\Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface[]
     */
    private $availablePrices;


    /**
     * Constructor.
     *
     * @param ShipmentPriceResolverInterface    $priceResolver
     * @param ShipmentMethodRepositoryInterface $methodRepository
     */
    public function __construct(
        ShipmentPriceResolverInterface $priceResolver,
        ShipmentMethodRepositoryInterface $methodRepository
    ) {
        $this->priceResolver = $priceResolver;
        $this->methodRepository = $methodRepository;
    }

    /**
     * Builds the choices.
     *
     * @param SaleInterface|null $sale
     * @param bool               $availableOnly
     * @param bool               $withPrice
     *
     * @return array|\Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface[]
     */
    private function buildChoices(SaleInterface $sale = null, $withPrice = true, $availableOnly = true)
    {
        if (null !== $this->availableMethods) {
            return $this->availableMethods;
        }

        if (null !== $sale) {
            $this->availablePrices = $this->priceResolver->getAvailablePricesBySale($sale);

            if ($withPrice) {
                $this->availableMethods = [];

                foreach ($this->availablePrices as $price) {
                    $this->availableMethods[] = $price->getMethod();
                }

                return $this->availableMethods;
            }
        }

        $sorting = ['position' => 'ASC'];
        $criteria = $availableOnly ? ['available' => true, 'enabled' => true] : ['enabled' => true];
        $this->availableMethods = (array)$this->methodRepository->findBy($criteria, $sorting);

        return $this->availableMethods;
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
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface $price */
        if (null !== $price = $this->findPriceByMethod($method)) {
            return [
                'data-price' => $price->getNetPrice(),
            ];
        }

        return [];
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
            return sprintf(
                '%s (%s)',
                $method->getTitle(),
                number_format($price->getNetPrice(), 2, ',', '') . ' €' // TODO localized currency
            );
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
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $choices = function (Options $options) {
            return $this->buildChoices($options['sale'], $options['with_price'], $options['available']);
        };

        $resolver
            ->setDefaults([
                'label'        => 'ekyna_commerce.shipment_method.label.singular',
                'sale'         => null,
                'with_price'   => true,
                'available'    => true,
                'choices'      => $choices,
                'choice_attr'  => [$this, 'buildChoiceAttr'],
                'choice_label' => [$this, 'buildChoiceLabel'],
            ])
            ->setAllowedTypes('sale', ['null', SaleInterface::class])
            ->setAllowedTypes('with_price', 'bool')
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
