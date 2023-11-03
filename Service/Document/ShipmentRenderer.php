<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

use function in_array;

/**
 * Class ShipmentRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property ShipmentInterface $subject
 */
class ShipmentRenderer extends AbstractRenderer
{
    private string $type;

    public function __construct(object $subject, string $type)
    {
        parent::__construct($subject);

        if (!in_array($type, DocumentTypes::getShipmentTypes(), true)) {
            throw new InvalidArgumentException("Unexpected shipment document type '$type'.");
        }

        if ($type === DocumentTypes::TYPE_SHIPMENT_FORM && $subject->isReturn()) {
            throw new InvalidArgumentException("Unexpected shipment document type '$type'.");
        }

        $this->type = $type;
    }

    public function getFilename(): string
    {
        return $this->type . '_' . $this->subject->getNumber();
    }

    protected function getParameters(): array
    {
        return [
            'type' => $this->type,
        ];
    }

    protected function supports(object $subject): bool
    {
        return $subject instanceof ShipmentInterface;
    }

    protected function getTemplate(): string
    {
        return $this->type === DocumentTypes::TYPE_SHIPMENT_FORM
            ? '@EkynaCommerce/Document/shipment_form.html.twig'
            : '@EkynaCommerce/Document/shipment.html.twig';
    }
}
