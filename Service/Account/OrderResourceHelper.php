<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Account;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderAttachmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderPaymentRepositoryInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class OrderResourceHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Account
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderResourceHelper
{
    public function __construct(
        private readonly CustomerProviderInterface  $customerProvider,
        private readonly RepositoryFactoryInterface $repositoryFactory
    ) {
    }

    public function getCustomer(): CustomerInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->customerProvider->getCustomer();
    }

    /**
     * @return array<int, OrderInterface>
     */
    public function findOrdersByCustomer(CustomerInterface $customer): array
    {
        /** @var OrderRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(OrderInterface::class);

        if ($customer->hasParent()) {
            if ($customer->isCanReadParentOrders()) {
                return $repository->findByCustomer($customer->getParent());
            }

            return $repository->findByOriginCustomer($customer);
        }

        return $repository->findByCustomer($customer);
    }

    public function findOrderByCustomerAndNumber(CustomerInterface $customer, string $number): OrderInterface
    {
        /** @var OrderRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(OrderInterface::class);

        $order = $repository->findOneByCustomerAndNumber($customer, $number);

        if (!$order) {
            throw new NotFoundHttpException('Order not found.');
        }

        return $order;
    }

    public function findPaymentByOrderAndKey(OrderInterface $order, string $key): OrderPaymentInterface
    {
        /** @var OrderPaymentRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(OrderPaymentInterface::class);

        $payment = $repository->findOneByOrderAndKey($order, $key);

        if (!$payment) {
            throw new NotFoundHttpException('Payment not found.');
        }

        return $payment;
    }

    public function findShipmentByOrderAndId(OrderInterface $order, int $id): OrderShipmentInterface
    {
        $shipment = $this
            ->repositoryFactory
            ->getRepository(OrderShipmentInterface::class)
            ->findOneBy([
                'order' => $order,
                'id'    => $id,
            ]);

        if (null === $shipment) {
            throw new NotFoundHttpException('Shipment not found.');
        }

        return $shipment;
    }

    public function findInvoiceByOrderAndId(OrderInterface $order, int $id): OrderInvoiceInterface
    {
        $invoice = $this
            ->repositoryFactory
            ->getRepository(OrderInvoiceInterface::class)
            ->findOneBy([
                'order' => $order,
                'id'    => $id,
            ]);

        if (null === $invoice) {
            throw new NotFoundHttpException('Invoice not found.');
        }

        return $invoice;
    }

    public function findAttachmentByOrderAndId(OrderInterface $order, int $id): OrderAttachmentInterface
    {
        $attachment = $this
            ->repositoryFactory
            ->getRepository(OrderAttachmentInterface::class)
            ->findOneBy([
                'order' => $order,
                'id'    => $id,
            ]);

        if (null === $attachment) {
            throw new NotFoundHttpException('Attachment not found.');
        }

        return $attachment;
    }
}
