<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Export;

use Ekyna\Bundle\UiBundle\Form\Type\DateRangeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class RangeExportType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RangeExportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('submit', SubmitType::class, [
                'label'        => t('button.export', [], 'EkynaUi'),
                'button_class' => 'default',
                'attr'         => [
                    'icon' => 'download',
                ],
            ]);
    }

    public function getParent(): string
    {
        return DateRangeType::class;
    }
}
