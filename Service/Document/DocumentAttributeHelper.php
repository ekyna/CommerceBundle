<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Class DocumentAttributeHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentAttributeHelper
{
    public function __construct(
        private readonly string $defaultLocale
    ) {
    }

    /**
     * Returns the document locale.
     *
     * @param object $document
     *
     * @return string
     */
    public function getLocale(object $document): string
    {
        if ($document instanceof DocumentInterface) {
            return $document->getLocale();
        }

        if ($document instanceof ShipmentInterface) {
            return $document->getLocale();
        }

        return $this->defaultLocale;
    }

    /**
     * Returns the document type.
     *
     * @param object      $document
     * @param string|null $default
     *
     * @return string|null
     */
    public function getType(object $document, string $default = null): ?string
    {
        if ($document instanceof DocumentInterface) {
            return $document->getType();
        }

        return $default;
    }

    /**
     * Returns the document sale.
     *
     * @param object $document
     *
     * @return SaleInterface|null
     */
    public function getSale(object $document): ?SaleInterface
    {
        if ($document instanceof DocumentInterface) {
            return $document->getSale();
        }

        if ($document instanceof ShipmentInterface) {
            return $document->getSale();
        }

        return null;
    }

    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }
}
