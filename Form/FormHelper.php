<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form;

use Ekyna\Component\Commerce\Common\Model\Units;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

use function array_replace_recursive;
use function Symfony\Component\Translation\t;

/**
 * Class FormHelper
 * @package Ekyna\Bundle\CommerceBundle\Form
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class FormHelper
{
    public static function addQuantityType(
        FormInterface|FormBuilderInterface $form,
        string                             $unit = Units::PIECE,
        array                              $options = []
    ): void {
        $options = array_replace_recursive([
            'label'   => t('field.quantity', [], 'EkynaUi'),
            'decimal' => true,
            'attr'    => [
                'autocomplete' => 'off',
            ],
        ], $options);

        if (Units::PIECE === $unit) {
            $type = IntegerType::class;
        } else {
            $type = NumberType::class;
            $options['scale'] = Units::getPrecision($unit);
        }

        $form->add('quantity', $type, $options);
    }
}
