<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Quote;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class QuoteProjectTrustType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Quote
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteProjectTrustType extends AbstractType
{
    public const CHOICES = [
        '10%'  => 1,
        '30%'  => 3,
        '60%'  => 6,
        '90%'  => 9,
        '100%' => 10,
    ];

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'    => t('quote.field.project_trust', [], 'EkynaCommerce'),
            'required' => false,
            'select2'  => false,
            'choices'  => self::CHOICES,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
