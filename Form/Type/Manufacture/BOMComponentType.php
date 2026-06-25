<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Manufacture;

use Ekyna\Bundle\CommerceBundle\Form\Type\Subject\SubjectChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionPositionType;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class BOMComponentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Manufacture
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BOMComponentType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subjectIdentity', SubjectChoiceType::class, [
                'lock_mode' => false,
                'context'   => SubjectProviderInterface::CONTEXT_SUPPLIER,
            ])
            ->add('quantity', NumberType::class, [
                'label'          => t('field.quantity', [], 'EkynaUi'),
                'decimal'        => true,
                'scale'          => 3, // TODO Packaging format
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'order-item-quantity',
                ],
            ])
            ->add('position', CollectionPositionType::class);

        //FormHelper::addQuantityType($builder, $unit);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_bom_component';
    }
}
