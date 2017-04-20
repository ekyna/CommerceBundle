<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Action;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Table\Action\AbstractActionType;
use Ekyna\Component\Table\Action\ActionInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_map;
use function Symfony\Component\Translation\t;

/**
 * Class ShipmentPrepareActionType
 * @package Ekyna\Bundle\CommerceBundle\Table\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPrepareActionType extends AbstractActionType
{
    private EntityManagerInterface $entityManager;

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

        $shipments = array_map(function (RowInterface $row) {
            return $row->getData(null);
        }, $rows);

        /** @var ShipmentInterface $shipment */
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('label', t('shipment.action.prepare', [], 'EkynaCommerce'));
    }
}
