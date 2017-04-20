<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form;

use Ekyna\Component\Commerce\Common\Model\Units;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

use function array_replace;
use function Symfony\Component\Translation\t;

/**
 * Class FormHelper
 * @package Ekyna\Bundle\CommerceBundle\Form
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class FormHelper
{
    /**
     * @param FormInterface|FormBuilderInterface $form
     */
    public static function addQuantityType($form, string $unit, array $options = []): void
    {
        $options = array_replace([
            'label'   => t('field.quantity', [], 'EkynaUi'),
            'decimal' => true,
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
