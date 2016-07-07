<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\StateHelper;

/**
 * Class ShipmentExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentExtension extends \Twig_Extension
{
    /**
     * @var StateHelper
     */
    private $stateHelper;


    /**
     * Constructor.
     *
     * @param StateHelper $stateHelper
     */
    public function __construct(StateHelper $stateHelper)
    {
        $this->stateHelper = $stateHelper;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('shipment_state_label', [$this->stateHelper, 'renderShipmentStateLabel'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('shipment_state_badge', [$this->stateHelper, 'renderShipmentStateBadge'], ['is_safe' => ['html']]),
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
