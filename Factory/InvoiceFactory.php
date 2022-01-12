<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Factory;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\ResourceFactory;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class InvoiceFactory
 * @package Ekyna\Bundle\CommerceBundle\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceFactory extends ResourceFactory
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function create(): ResourceInterface
    {
        /** @var InvoiceInterface $invoice */
        $invoice = parent::create();

        if (null === $request = $this->requestStack->getMainRequest()) {
            return $invoice;
        }

        $invoice->setCredit($request->query->getBoolean('credit'));

        return $invoice;
    }
}
