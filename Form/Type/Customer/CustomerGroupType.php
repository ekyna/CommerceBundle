<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatDisplayModeType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Component\Commerce\Customer\Entity\CustomerGroupTranslation;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class CustomerGroupType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label'    => t('field.name', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('business', Type\CheckboxType::class, [
                'label'    => t('customer_group.field.business', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('registration', Type\CheckboxType::class, [
                'label'    => t('customer_group.field.registration', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('quoteAllowed', Type\CheckboxType::class, [
                'label'    => t('customer_group.field.quote_allowed', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('loyalty', Type\CheckboxType::class, [
                'label'    => t('customer.field.loyalty_points', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('vatDisplayMode', VatDisplayModeType::class)
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => CustomerGroupTranslationType::class,
                'form_options'   => [
                    'data_class' => CustomerGroupTranslation::class,
                ],
                'label'          => false,
                'error_bubbling' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var CustomerGroupInterface $group */
            $group = $event->getData();
            $form = $event->getForm();

            $form->add('default', Type\CheckboxType::class, [
                'label'    => t('field.default', [], 'EkynaUi'),
                'required' => false,
                'disabled' => $group->isDefault(),
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);
        });
    }
}
