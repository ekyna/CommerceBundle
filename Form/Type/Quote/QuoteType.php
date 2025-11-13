<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Quote;

use Ekyna\Bundle\AdminBundle\Form\Type\UserChoiceType;
use Ekyna\Bundle\CmsBundle\Form\Type\TagChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IncotermType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerSearchType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleAddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceSearchType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class QuoteType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Quote
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteType extends SaleType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('invoiceAddress', SaleAddressType::class, [
                'label'          => t('sale.field.invoice_address', [], 'EkynaCommerce'),
                'address_type'   => $options['address_type'],
                'inherit_data'   => true,
                'mode'           => SaleAddressType::MODE_INVOICE,
                'customer_field' => 'customer',
                'required'       => false,
            ])
            ->add('destinationAddress', SaleAddressType::class, [
                'label'          => t('sale.field.destination_address', [], 'EkynaCommerce'),
                'address_type'   => $options['address_type'],
                'inherit_data'   => true,
                'mode'           => SaleAddressType::MODE_DESTINATION,
                'customer_field' => 'customer',
                'required'       => false,
            ])
            ->add('project', ResourceSearchType::class, [
                'resource' => 'ekyna_commerce.project',
                'required' => false,
            ])
            ->add('initiatorCustomer', CustomerSearchType::class, [
                'label'    => t('sale.field.initiator_customer', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('followerCustomer', CustomerSearchType::class, [
                'label'    => t('sale.field.follower_customer', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('inCharge', UserChoiceType::class, [
                'label'    => t('customer.field.in_charge', [], 'EkynaCommerce'),
                'roles'    => [],
                'required' => false,
            ])
            ->add('incoterm', IncotermType::class)
            ->add('editable', CheckboxType::class, [
                'label'    => t('quote.field.editable', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('expiresAt', DateTimeType::class, [
                'label' => t('field.expires_at', [], 'EkynaUi'),
            ])
            ->add('projectDate', DateType::class, [
                'label'    => t('quote.field.project_date', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('projectTrust', IntegerType::class, [
                'label'    => t('quote.field.project_trust', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'min' => 1,
                    'max' => 10,
                ],
            ])
            ->add('projectAlive', ChoiceType::class, [
                'label'                     => t('quote.field.project_alive', [], 'EkynaCommerce'),
                'choices'                   => [
                    'value.yes' => '1',
                    'value.no'  => '0',
                ],
                'choice_translation_domain' => 'EkynaUi',
                'expanded'                  => true,
                'required'                  => false,
                'placeholder'               => t('value.undefined', [], 'EkynaUi'),
                'attr'                      => [
                    'class'             => 'inline',
                    'align_with_widget' => true,
                ],
            ])
            ->add('tags', TagChoiceType::class);

        FormUtil::bindFormEventsToChildren(
            $builder,
            [
                FormEvents::POST_SET_DATA => 0,
                FormEvents::PRE_SET_DATA  => 2048,
                FormEvents::PRE_SUBMIT    => 2048,
                FormEvents::POST_SUBMIT   => 2048,
            ],
            ['invoiceAddress', 'destinationAddress']
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('address_type', QuoteAddressType::class);
    }
}
