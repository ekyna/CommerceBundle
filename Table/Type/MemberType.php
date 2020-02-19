<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\Column\ConstantChoiceType;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Newsletter\Model\MemberStatuses;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Filter\EntityType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class MemberType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MemberType extends ResourceTableType
{
    /**
     * @var string
     */
    private $audienceClass;


    /**
     * Constructor.
     *
     * @param string $memberClass
     * @param string $audienceClass
     */
    public function __construct(string $memberClass, string $audienceClass)
    {
        parent::__construct($memberClass);

        $this->audienceClass = $audienceClass;
    }

    /**
     * @inheritDoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('email', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.email_address',
                'route_name'           => 'ekyna_commerce_member_admin_show',
                'route_parameters_map' => [
                    'memberId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('audience', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_commerce.audience.label.plural',
                'route_name'           => 'ekyna_commerce_audience_admin_show',
                'route_parameters_map' => ['audienceId' => 'audience.id'],
                'position'             => 20,
            ])
            ->addColumn('status', ConstantChoiceType::class, [
                'label'    => 'ekyna_core.field.status',
                'class'    => MemberStatuses::class,
                'theme'    => true,
                'position' => 30,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'icon'                 => 'pencil',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_member_admin_edit',
                        'route_parameters_map' => [
                            'memberId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'icon'                 => 'trash',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_member_admin_remove',
                        'route_parameters_map' => [
                            'memberId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('email', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.email',
                'position' => 10,
            ])
            ->addFilter('audience', EntityType::class, [
                'label'        => 'ekyna_commerce.audience.label.plural',
                'class'        => $this->audienceClass,
                'entity_label' => 'name',
                'position'     => 20,
            ])
            ->addFilter('status', CType\Filter\ChoiceType::class, [
                'label'    => 'ekyna_core.field.status',
                'choices'  => MemberStatuses::getChoices(),
                'position' => 30,
            ]);
    }
}
