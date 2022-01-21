<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Filter;

use Ekyna\Bundle\AdminBundle\Table\Type\Filter\ConstantChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\PaymentStates as BStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates as CStates;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SalePaymentStateType
 * @package Ekyna\Bundle\CommerceBundle\Table\Filter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SalePaymentStateType extends AbstractFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'  => t('sale.field.payment_state', [], 'EkynaCommerce'),
            'class'  => BStates::class,
            'filter' => [
                CStates::STATE_EXPIRED,
                CStates::STATE_SUSPENDED,
                CStates::STATE_UNKNOWN,
            ],
        ]);
    }

    public function getParent(): ?string
    {
        return ConstantChoiceType::class;
    }
}
