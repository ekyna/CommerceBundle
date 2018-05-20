<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\CommerceBundle\Model\VatDisplayModes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VatDisplayModeType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VatDisplayModeType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'       => 'ekyna_commerce.pricing.field.vat_display_mode',
            'choices'     => VatDisplayModes::getChoices(),
            'placeholder' => 'ekyna_core.field.default',
            'required'    => false,
            'expanded'    => true,
            'attr'        => [
                'inline'            => true,
                'align_with_widget' => true,
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
