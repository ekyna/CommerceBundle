<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

use function Symfony\Component\Translation\t;

/**
 * Class SaleItemPrioritizeType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemPrioritizeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', NumberType::class, [
                'label'       => t('field.quantity', [], 'EkynaUi'),
                'decimal'     => true,
                'scale'       => 3, // TODO Packaging format
                'constraints' => [
                    new NotBlank(),
                    new GreaterThan(0),
                    new LessThanOrEqual($options['max_quantity']),
                ],
            ])
            ->add('same_sale', CheckboxType::class, [
                'label'    => t('sale.prioritize.same_sale', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('max_quantity');
    }
}
