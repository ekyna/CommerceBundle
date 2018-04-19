<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Action;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Preparer\SalePreparerInterface;
use Ekyna\Component\Table\Action\AbstractActionType;
use Ekyna\Component\Table\Action\ActionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrderAbortActionType
 * @package Ekyna\Bundle\CommerceBundle\Table\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAbortActionType extends AbstractActionType
{
    /**
     * @var SalePreparerInterface
     */
    private $salePreparer;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    /**
     * Constructor.
     *
     * @param SalePreparerInterface  $salePreparer
     * @param EntityManagerInterface $entityManager
     */
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
            /** @var \Ekyna\Component\Commerce\Order\Model\OrderInterface $order */
            $order = $row->getData();

            $shipment = $this->salePreparer->abort($order);

            if (null !== $shipment) {
                $this->entityManager->remove($shipment);
                $this->entityManager->flush();
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('label', 'ekyna_commerce.sale.action.abort');
    }
}
