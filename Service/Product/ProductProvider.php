<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Product;

use Ekyna\Bundle\CoreBundle\Form\Type\EntitySearchType;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Order\Entity\OrderItemAdjustment;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectIdentity;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class ProductProvider
 * @package Ekyna\Bundle\CommerceBundle\Service\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductProvider implements SubjectProviderInterface
{
    /**
     * @var TaxResolverInterface
     */
    private $taxResolver;

    /**
     * @var string
     */
    private $productClass;


    /**
     * Constructor.
     *
     * @param TaxResolverInterface $taxResolver
     * @param string $productClass
     */
    public function __construct(TaxResolverInterface $taxResolver, $productClass)
    {
        $this->taxResolver = $taxResolver;
        $this->productClass = $productClass;
    }

    /**
     * @inheritdoc
     */
    public function buildChoiceForm(FormInterface $form)
    {
        // TODO: Implement buildChoiceForm() method.

        $form->add('choice', EntitySearchType::class, [
            'label'           => 'ekyna_commerce.product.label.singular',
            'class'           => $this->productClass,
            'search_route'    => 'ekyna_commerce_product_admin_search',
            'find_route'      => 'ekyna_commerce_product_admin_find',
            'allow_clear'     => false,
            'format_function' =>
                "if(!data.id)return 'Rechercher';" .
                "return $('<span>'+data.designation+'</span>');",
            'required'        => false,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function buildItemForm(FormInterface $form)
    {
        // TODO: Implement buildItemForm() method.
    }

    /**
     * @inheritdoc
     */
    public function setItemDefaults(SaleItemInterface $item, $subject)
    {
        /** @var \Ekyna\Component\Commerce\Product\Model\ProductInterface $subject */
        $identity = new SubjectIdentity();
        $identity
            ->setId($subject->getId())
            ->setClass(get_class($subject));

        $item
            ->setSubject($subject)
            ->setSubjectIdentity($identity)
            ->setDesignation($subject->getDesignation())
            ->setReference($subject->getReference())
            ->setNetPrice($subject->getNetPrice())
            ->setQuantity(1)
            ->setWeight($subject->getWeight());

        $sale = $item->getSale();
        $customer = $sale->getCustomer();
        $address = $sale->getDeliveryAddress();
        $taxGroup = $subject->getTaxGroup();

        if (null !== $customer && null !== $address) {
            $taxes = $this->taxResolver->getApplicableTaxesByTaxGroupAndCustomerGroups(
                $taxGroup, $customer->getCustomerGroups(), $address
            );

            // TODO $this->adjustmentFactory->buildTaxationAdjustments($item, $taxes);

            // TODO temporary
            foreach ($taxes as $tax) {
                $adjustment = new OrderItemAdjustment();
                $adjustment
                    ->setMode(AdjustmentModes::MODE_PERCENT)
                    ->setType(AdjustmentTypes::TYPE_TAXATION)
                    ->setDesignation($tax->getName())
                    ->setAmount($tax->getRate());

                /** @var \Ekyna\Component\Commerce\Order\Model\OrderItemInterface $item */
                $item->addAdjustment($adjustment);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function supports($subject)
    {
        return $subject instanceof ProductInterface;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'product';
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return 'ekyna_commerce.product.label.singular';
    }
}
