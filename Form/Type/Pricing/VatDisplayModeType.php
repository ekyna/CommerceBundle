<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\CommerceBundle\Model\VatDisplayModes;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class VatDisplayModeType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VatDisplayModeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'              => t('pricing.field.vat_display_mode', [], 'EkynaCommerce'),
            'class'              => VatDisplayModes::class,
            'required'           => false,
            'expanded'           => true,
            'attr'               => [
                'inline'            => true,
                'align_with_widget' => true,
            ],
        ]);
    }

    public function getParent(): ?string
    {
        return ConstantChoiceType::class;
    }
}
