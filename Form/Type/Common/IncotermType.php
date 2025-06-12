<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Component\Commerce\Common\Model\Incoterm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class IncotermType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class IncotermType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'             => t('field.incoterm', [], 'EkynaCommerce'),
            'required'          => false,
            'class'             => Incoterm::class,
            'preferred_choices' => [Incoterm::DAP],
        ]);
    }

    public function getParent(): ?string
    {
        return EnumType::class;
    }
}
