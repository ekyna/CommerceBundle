<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Quote;

use Ekyna\Bundle\AdminBundle\Form\Type\UserChoiceType;
use Ekyna\Bundle\CmsBundle\Form\Type\TagChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerSearchType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleType;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceSearchType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use function Symfony\Component\Translation\t;

/**
 * Class QuoteType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Quote
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteType extends SaleType
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        string                                         $defaultCurrency
    ) {
        parent::__construct($defaultCurrency);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('project', ResourceSearchType::class, [
                'resource' => 'ekyna_commerce.project',
                'required' => false,
            ])
            ->add('initiatorCustomer', CustomerSearchType::class, [
                'label'    => t('sale.field.initiator_customer', [], 'EkynaCommerce'),
                'required' => false,
            ])
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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var OrderInterface $order */
            $order = $event->getData();
            $form = $event->getForm();

            $form->add('inCharge', UserChoiceType::class, [
                'label'    => t('customer.field.in_charge', [], 'EkynaCommerce'),
                'roles'    => [],
                'required' => false,
                'disabled' => $order->getInCharge() && !$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN'),
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('address_type', QuoteAddressType::class);
    }
}
