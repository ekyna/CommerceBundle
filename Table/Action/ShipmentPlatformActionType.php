<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Action;

use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentHelper;
use Ekyna\Component\Table\Action\AbstractActionType;
use Ekyna\Component\Table\Action\ActionInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentPlatformActionType
 * @package Ekyna\Bundle\CommerceBundle\Table\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPlatformActionType extends AbstractActionType
{
    /**
     * @var ShipmentHelper
     */
    private $shipmentHelper;


    /**
     * Constructor.
     *
     * @param ShipmentHelper $shipmentHelper
     */
    public function __construct(ShipmentHelper $shipmentHelper)
    {
        $this->shipmentHelper = $shipmentHelper;
    }

    /**
     * @inheritDoc
     */
    public function execute(ActionInterface $action, array $options)
    {
        $platformName = $options['platform'];
        $actionName = $options['action'];

        $table = $action->getTable();

        // The selected row's
        $rows = $table->getSourceAdapter()->getSelection(
            $table->getContext()
        );

        $shipments = array_map(function(RowInterface $row) {
            return $row->getData();
        }, $rows);

        $response = $this->shipmentHelper->executePlatformAction($platformName, $actionName, $shipments);
        if (null !== $response) {
            return $response;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'platform' => null,
                'action'   => null,
            ])
            ->setAllowedTypes('platform', 'string')
            ->setAllowedTypes('action', 'string');
    }
}
