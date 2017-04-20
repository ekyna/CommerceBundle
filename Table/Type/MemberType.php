<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\AdminBundle\Table\Type\Column\ConstantChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\SubscriptionStatus;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class MemberType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MemberType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('email', BType\Column\AnchorType::class, [
                'label'    => t('field.email_address', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addColumn('status', ConstantChoiceType::class, [
                'label'    => t('field.status', [], 'EkynaUi'),
                'class'    => SubscriptionStatus::class,
                'theme'    => true,
                'position' => 30,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    UpdateAction::class,
                    DeleteAction::class,
                ],
            ])
            ->addFilter('email', CType\Filter\TextType::class, [
                'label'    => t('field.email', [], 'EkynaUi'),
                'position' => 10,
            ]);
    }
}
