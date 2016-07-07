<?php

namespace Ekyna\Bundle\CommerceBundle;

use Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler\AdminMenuPass;
use Ekyna\Bundle\CoreBundle\AbstractBundle;
use Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\Compiler\ConfigureValidatorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EkynaCommerceBundle
 * @package Ekyna\Bundle\CommerceBundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EkynaCommerceBundle extends AbstractBundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AdminMenuPass());
        $container->addCompilerPass(new ConfigureValidatorPass());
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelInterfaces()
    {
        return [
            'Ekyna\Component\Commerce\Customer\Model\CustomerInterface'        => 'ekyna_commerce.customer.class',
            'Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface' => 'ekyna_commerce.customer_address.class',

            'Ekyna\Component\Commerce\Order\Model\OrderInterface'        => 'ekyna_commerce.order.class',
            'Ekyna\Component\Commerce\Order\Model\OrderAddressInterface' => 'ekyna_commerce.order_address.class',

            'Ekyna\Component\Commerce\Payment\Model\PaymentInterface'       => 'ekyna_commerce.payment.class',
            'Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface' => 'ekyna_commerce.payment_method.class',

            'Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface'       => 'ekyna_commerce.shipment.class',
            'Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface'   => 'ekyna_commerce.shipment_item.class',
            'Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface' => 'ekyna_commerce.shipment_method.class',
        ];
    }
}
