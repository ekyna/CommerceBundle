<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantHelper;

/**
 * Class ShipmentExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentExtension extends \Twig_Extension
{
    /**
     * @var \Ekyna\Bundle\CommerceBundle\Service\ConstantHelper
     */
    private $constantHelper;


    /**
     * Constructor.
     *
     * @param \Ekyna\Bundle\CommerceBundle\Service\ConstantHelper $constantHelper
     */
    public function __construct(ConstantHelper $constantHelper)
    {
        $this->constantHelper = $constantHelper;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('shipment_state_label', [$this->constantHelper, 'renderShipmentStateLabel'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('shipment_state_badge', [$this->constantHelper, 'renderShipmentStateBadge'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_shipment';
    }
}
