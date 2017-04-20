<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatDisplayModeType;
use Ekyna\Component\Commerce\Common\Context\Context;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class ContextType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ContextType extends AbstractType
{
    private string $class = Context::class;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currency', CurrencyChoiceType::class)
            ->add('deliveryCountry', CountryChoiceType::class, [
                'label' => t('context.field.delivery_country', [], 'EkynaCommerce'),
            ]);

        if ($options['admin_mode']) {
            $builder
                ->add('invoiceCountry', CountryChoiceType::class, [
                    'label' => t('context.field.invoice_country', [], 'EkynaCommerce'),
                ])
                ->add('customerGroup', CustomerGroupChoiceType::class)
                ->add('vatDisplayMode', VatDisplayModeType::class)
                ->add('date', DateTimeType::class, [
                    'required' => false,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->class,
        ]);
    }
}
