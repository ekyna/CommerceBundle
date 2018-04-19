<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Action;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Table\Action\AbstractActionType;
use Ekyna\Component\Table\Action\ActionInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentPrepareActionType
 * @package Ekyna\Bundle\CommerceBundle\Table\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPrepareActionType extends AbstractActionType
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritDoc
     */
    public function execute(ActionInterface $action, array $options)
    {
        $table = $action->getTable();

        // The selected row's
        $rows = $table->getSourceAdapter()->getSelection(
            $table->getContext()
        );

        $shipments = array_map(function(RowInterface $row) {
            return $row->getData();
        }, $rows);

        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
        foreach ($shipments as $shipment) {
            $state = $shipment->getState();
            if (!(ShipmentStates::isPreparableState($state) || $state === ShipmentStates::STATE_CANCELED)) {
                continue;
            }

            $shipment->setState(ShipmentStates::STATE_PREPARATION);

            $this->entityManager->persist($shipment);
        }

        $this->entityManager->flush();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('label', 'ekyna_commerce.shipment.action.prepare');
    }
}
