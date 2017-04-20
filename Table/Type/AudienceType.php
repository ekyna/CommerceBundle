<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Action;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class AudienceType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AudienceType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label' => t('field.name', [], 'EkynaUi'),
            ])
            ->addColumn('public', CType\Column\BooleanType::class, [
                'label'       => t('audience.field.public', [], 'EkynaCommerce'),
                'property'    => 'public',
                'true_class'  => 'label-primary',
                'false_class' => 'label-default',
                'position'    => 20,
            ])
            ->addColumn('default', CType\Column\BooleanType::class, [
                'label'       => t('field.default', [], 'EkynaUi'),
                'property'    => 'default',
                'true_class'  => 'label-primary',
                'false_class' => 'label-default',
                'position'    => 20,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    Action\UpdateAction::class,
                    Action\DeleteAction::class => [
                        'disable' => function (RowInterface $row) {
                            /** @var AudienceInterface $audience */
                            $audience = $row->getData(null);

                            return $audience->isDefault();
                        },
                    ],
                ],
            ]);
    }
}
