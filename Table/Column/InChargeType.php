<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Column\EntityType;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InChargeType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InChargeType extends AbstractColumnType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'                => 'ekyna_commerce.customer.field.in_charge',
            'entity_label'         => 'shortName',
            'route_name'           => 'ekyna_admin_user_admin_show',
            'route_parameters_map' => ['userId' => 'id'],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return EntityType::class;
    }
}
