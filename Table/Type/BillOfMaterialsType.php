<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\AdminBundle\Table\Type as AType;
use Ekyna\Bundle\CommerceBundle\Action\Admin\BillOfMaterials\CreatePOAction;
use Ekyna\Bundle\CommerceBundle\Table as Type;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Manufacture\Model\BOMState;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class BillOfMaterialsType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BillOfMaterialsType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $subject = $options['subject'];

        if (null !== $subject) {
            $this->buildForSubject($builder, $subject);
        } else {
            $builder
                ->setExportable(true)
                ->setConfigurable(true)
                ->setProfileable(true)
                ->addColumn('subject', Type\Column\SubjectReferenceType::class, [
                    'position' => 20,
                ])
                ->addFilter('subject', Type\Filter\SubjectReferenceType::class, [
                    'context'  => SubjectProviderInterface::CONTEXT_SUPPLIER,
                    'position' => 20,
                ]);
        }

        $builder
            ->addDefaultSort('id', ColumnSort::DESC)
            ->addColumn('number', BType\Column\AnchorType::class, [
                'label'    => t('field.number', [], 'EkynaUi'),
                'summary'  => true,
                'sortable' => true,
                'position' => 10,
            ])
            ->addColumn('version', CType\Column\NumberType::class, [
                'label'    => t('field.version', [], 'EkynaUi'),
                'sortable' => true,
                'position' => 30,
            ])
            ->addColumn('state', AType\Column\EnumType::class, [
                'label'    => t('field.status', [], 'EkynaUi'),
                'sortable' => true,
                'position' => 40,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    CreatePOAction::class => [
                        'disable' => function(RowInterface $row): bool {
                            $bom = $row->getData(null);
                            return $bom->getState() !== BOMState::VALIDATED;
                        },
                    ],
                    UpdateAction::class,
                    DeleteAction::class,
                ]
            ])
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => t('field.number', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addFilter('version', CType\Filter\NumberType::class, [
                'label'    => t('field.version', [], 'EkynaUi'),
                'position' => 30,
            ])
            ->addFilter('state', AType\Filter\EnumType::class, [
                'label'    => t('field.status', [], 'EkynaUi'),
                'class'    => BOMState::class,
                'position' => 40,
            ]);
    }

    private function buildForSubject(TableBuilderInterface $builder, SubjectInterface $subject): void
    {
        $source = $builder->getSource();
        if (!$source instanceof EntitySource) {
            return;
        }

        $source->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias) use ($subject): void {
            $qb
                ->andWhere($qb->expr()->eq($alias . '.subjectIdentity.provider', ':provider'))
                ->andWhere($qb->expr()->eq($alias . '.subjectIdentity.identifier', ':identifier'))
                ->setParameter('provider', $subject::getProviderName())
                ->setParameter('identifier', $subject->getId());
        });

        $builder->setPerPageChoices([100]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('subject', null)
            ->setAllowedTypes('subject', ['null', SubjectInterface::class]);
    }
}
