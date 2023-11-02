<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\CommerceBundle\Table\Column\PaymentStateType;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class SupplierPaymentType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierPaymentType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('amount', BType\Column\PriceType::class, [
                'label'         => t('field.amount', [], 'EkynaUi'),
                'currency_path' => 'currency.code',
                'position'      => 10,
            ])
            ->addColumn('state', PaymentStateType::class, [
                'label'    => t('field.status', [], 'EkynaUi'),
                'position' => 20,
            ])
            ->addColumn('toForwarder', CType\Column\BooleanType::class, [
                'label'       => t('supplier_payment.field.to_forwarder', [], 'EkynaCommerce'),
                'true_class'  => 'label-warning',
                'false_class' => 'label-success',
                'position'    => 30,
            ])
            ->addColumn('exchangeDate', CType\Column\DateTimeType::class, [
                'label'    => t('field.date', [], 'EkynaUi'),
                'position' => 40,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
            ]);
    }
}
