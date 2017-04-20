<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Action;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Preparer\SalePreparerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Table\Action\AbstractActionType;
use Ekyna\Component\Table\Action\ActionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class OrderPrepareActionType
 * @package Ekyna\Bundle\CommerceBundle\Table\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderPrepareActionType extends AbstractActionType
{
    private SalePreparerInterface $salePreparer;
    private EntityManagerInterface $entityManager;

    public function __construct(SalePreparerInterface $salePreparer, EntityManagerInterface $entityManager)
    {
        $this->salePreparer = $salePreparer;
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritDoc
     */
    public function execute(ActionInterface $action, array $options)
    {
        $table = $action->getTable();

        // The selected row's data
        $rows = $table->getSourceAdapter()->getSelection(
            $table->getContext()
        );

        foreach ($rows as $row) {
            /** @var OrderInterface $order */
            $order = $row->getData(null);

            $shipment = $this->salePreparer->prepare($order);

            if (null !== $shipment) {
                $this->entityManager->persist($order);
                $this->entityManager->flush();
            }
        }

        return true;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('label', t('sale.action.prepare', [], 'EkynaCommerce'));
    }
}
