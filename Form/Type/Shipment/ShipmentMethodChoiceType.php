<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentMethodRepositoryInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
     * @param SaleInterface|null $sale
     * @param bool               $availableOnly
     *
     * @return array|\Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface[]
     */
    private function buildChoices(SaleInterface $sale = null, $availableOnly = true)
    {
        if (null !== $this->availableMethods) {
            return $this->availableMethods;
        }

        $sorting = ['position' => 'ASC'];

        if (null !== $sale) { // With price resolution
            $this->availablePrices = $this->priceResolver->getAvailablePricesBySale($sale);
            if ($availableOnly) {
                // Only methods corresponding to a price
                $this->availableMethods = [];
                foreach ($this->availablePrices as $price) {
                    $this->availableMethods[] = $price->getMethod();
                }
            } else { // All methods
                $this->availableMethods = (array)$this->methodRepository->findBy([], $sorting);
            }
        } else { // Without price resolution
            $criteria = $availableOnly ? ['available' => true, 'enabled' => true] : [];

            $this->availableMethods = (array)$this->methodRepository->findBy($criteria, $sorting);
        }

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
     * @return array
     */
    public function buildChoiceLabel(ShipmentMethodInterface $method)
    {
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface $price */
        if (null !== $price = $this->findPriceByMethod($method)) {
            return sprintf(
                '%s (%s)',
                $price->getMethod()->getTitle(),
                number_format($price->getNetPrice(), 2, ',', '') . ' €' // TODO localized currency
            );
        }

        return $method->getTitle();
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $choices = function (Options $options) {
            return $this->buildChoices($options['sale'], !$options['admin_mode']);
        };

        $resolver
            ->setDefaults([
                'label'        => 'ekyna_commerce.shipment_method.label.singular',
                'sale'         => null,
                'choices'      => $choices,
                'choice_attr'  => [$this, 'buildChoiceAttr'],
                'choice_label' => [$this, 'buildChoiceLabel'],
                'attr'         => [
                    'class' => 'no-select2',
                ],
            ])
            ->setAllowedTypes('sale', ['null', SaleInterface::class]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
