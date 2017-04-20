<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class TaxGroupType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxGroupType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var TaxGroupInterface $group */
            $group = $event->getData();
            $form = $event->getForm();

            $disabled = !empty($group->getCode());

            $form
                ->add('default', Type\CheckboxType::class, [
                    'label'    => t('field.default', [], 'EkynaUi'),
                    'required' => false,
                    'disabled' => $group->isDefault(),
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('name', Type\TextType::class, [
                    'label'    => t('field.name', [], 'EkynaUi'),
                    'disabled' => $disabled,
                ])
                ->add('taxes', ResourceChoiceType::class, [
                    'resource'  => 'ekyna_commerce.tax',
                    'multiple'  => true,
                    'allow_new' => true,
                    'required'  => false,
                    'disabled'  => $disabled,
                ]);
        });
    }
}
