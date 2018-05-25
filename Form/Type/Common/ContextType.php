<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatDisplayModeType;
use Ekyna\Component\Commerce\Common\Context\Context;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContextType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ContextType extends AbstractType
{
    /**
     * @var string
     */
    private $class = Context::class;


    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customerGroup', CustomerGroupChoiceType::class)
            ->add('invoiceCountry', CountryChoiceType::class, [
                'label' => 'ekyna_commerce.context.field.invoice_country',
            ])
            ->add('deliveryCountry', CountryChoiceType::class, [
                'label' => 'ekyna_commerce.context.field.delivery_country',
            ])
            ->add('currency', CurrencyChoiceType::class)
            ->add('vatDisplayMode', VatDisplayModeType::class)
            ->add('date', DateTimeType::class, [
                'required' => false,
                'format'   => 'dd/MM/yyyy',
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->class,
        ]);
    }
}
