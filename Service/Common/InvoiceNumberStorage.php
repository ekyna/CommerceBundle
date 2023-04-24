<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Component\Commerce\Common\Generator\StorageInterface;
use Ekyna\Component\Commerce\Invoice\Repository\InvoiceRepositoryInterface;

/**
 * Class InvoiceNumberStorage
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceNumberStorage implements StorageInterface
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $repository,
        private readonly bool                       $credit
    ) {
    }

    public function read(): string
    {
        return $this->repository->findLatestNumber($this->credit);
    }

    public function write(string $data): void
    {
    }
}
