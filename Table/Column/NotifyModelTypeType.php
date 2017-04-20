<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Model\NotificationTypes;
use Ekyna\Bundle\TableBundle\Extension\Type\Column\AnchorType;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\Translation\t;

/**
 * Class NotifyModelTypeType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NotifyModelTypeType extends AbstractColumnType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $view->vars['value'] = NotificationTypes::getLabel($view->vars['value'])->trans($this->translator);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'    => t('field.type', [], 'EkynaUi'),
            'resource' => 'ekyna_commerce.notify_model',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'anchor';
    }

    public function getParent(): ?string
    {
        return AnchorType::class;
    }
}
