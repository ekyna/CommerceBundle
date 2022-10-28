<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Account;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteAttachmentInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuotePaymentInterface;
use Ekyna\Component\Commerce\Quote\Repository\QuotePaymentRepositoryInterface;
use Ekyna\Component\Commerce\Quote\Repository\QuoteRepositoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class QuoteResourceHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Account
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class QuoteResourceHelper
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
     * @return array<int, QuoteInterface>
     */
    public function findQuotesByCustomer(CustomerInterface $customer): array
    {
        /** @var QuoteRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(QuoteInterface::class);

        return $repository->findByCustomer($customer, [], true);
    }

    public function findQuoteByCustomerAndNumber(CustomerInterface $customer, string $number): ?QuoteInterface
    {
        /** @var QuoteRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(QuoteInterface::class);

        $quote = $repository->findOneByCustomerAndNumber($customer, $number);

        if (!$quote) {
            throw new NotFoundHttpException('Quote not found.');
        }

        return $quote;
    }

    public function findPaymentByQuoteAndKey(QuoteInterface $quote, string $key): ?QuotePaymentInterface
    {
        /** @var QuotePaymentRepositoryInterface $repository */
        $repository = $this->repositoryFactory->getRepository(QuotePaymentInterface::class);

        $payment = $repository->findOneByQuoteAndKey($quote, $key);

        if (!$payment) {
            throw new NotFoundHttpException('Payment not found.');
        }

        return $payment;
    }

    public function findAttachmentByQuoteAndId(QuoteInterface $quote, int $id): ?QuoteAttachmentInterface
    {
        $attachment = $this
            ->repositoryFactory
            ->getRepository(QuoteAttachmentInterface::class)
            ->findOneBy([
                'quote' => $quote,
                'id'    => $id,
            ]);

        if (!$attachment) {
            throw new NotFoundHttpException('Attachment not found.');
        }

        return $attachment;
    }
}
