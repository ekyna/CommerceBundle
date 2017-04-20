<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Watcher;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Payment\Model;
use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Watcher\OutstandingWatcher as BaseWatcher;

/**
 * Class OutstandingWatcher
 * @package Ekyna\Bundle\CommerceBundle\Service\Watcher
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OutstandingWatcher extends BaseWatcher
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var ResourceHelper
     */
    private $resourceHelper;

    /**
     * @var string
     */
    private $report;


    /**
     * Sets the manager.
     *
     * @param EntityManagerInterface $manager
     */
    public function setManager(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Sets the resource helper.
     *
     * @param ResourceHelper $resourceHelper
     */
    public function setResourceHelper(ResourceHelper $resourceHelper)
    {
        $this->resourceHelper = $resourceHelper;
    }

    /**
     * Returns the report.
     *
     * @return string
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @inheritDoc
     */
    public function watch(PaymentRepositoryInterface $paymentRepository)
    {
        $this->report = '';

        return parent::watch($paymentRepository);
    }

    /**
     * @inheritDoc
     */
    protected function persist(Model\PaymentInterface $payment)
    {
        $this->manager->persist($payment);

        $sale = $payment->getSale();

        $this->report .= sprintf(
            '<a href="%s">%s</a> payment state set to <em>outstanding</em>.<br>',
            $this->resourceHelper->generateResourcePath($sale, ReadAction::class, [], true),
            $payment->getNumber()
        );
    }
}
