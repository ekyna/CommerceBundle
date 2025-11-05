<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class SubjectReferenceType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectReferenceType extends AbstractColumnType
{
    public function __construct(
        private readonly SubjectHelperInterface $subjectHelper,
        private readonly ResourceHelper $resourceHelper,
    ) {
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $object = $row->getData($options['property_path']);

        $subject = $this->subjectHelper->resolve($object);

        $value = $subject->getReference() . ' - ' . $subject;

        try {
            $path = $this->resourceHelper->generateResourcePath($subject, ReadAction::class);
            $value = sprintf('<a href="%s">%s</a>', $path, $value);
        } catch (ResourceNotFoundException) {
        }

        $view->vars['value'] = $value;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'         => t('subject.label.singular', [], 'EkynaCommerce'),
            'property_path' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'text';
    }

    public function getParent(): ?string
    {
        return PropertyType::class;
    }
}
