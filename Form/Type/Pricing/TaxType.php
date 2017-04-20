<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class TaxType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var TaxInterface $tax */
            $tax = $event->getData();
            $form = $event->getForm();

            $disabled = !empty($tax->getCode());

            $form
                ->add('name', Type\TextType::class, [
                    'label'    => t('field.name', [], 'EkynaUi'),
                    'disabled' => $disabled,
                ])
                ->add('rate', Type\NumberType::class, [
                    'label'    => t('field.rate', [], 'EkynaUi'),
                    'decimal'  => true,
                    'scale'    => 2,
                    'disabled' => $disabled,
                    'attr'     => [
                        'input_group' => ['append' => '%'],
                    ],
                ])
                ->add('country', CountryChoiceType::class, [
                    'enabled'  => false,
                    'disabled' => $disabled,
                ])/*TODO->add('state', ResourceType::class, [
                    'label' => 'ekyna_commerce.state.label.singular',
                    'class' => $this->stateClass,
                ])*/
            ;
        });
    }
}
