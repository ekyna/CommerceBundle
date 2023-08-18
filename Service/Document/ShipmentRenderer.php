<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

use function array_filter;
use function in_array;

/**
 * Class ShipmentRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentRenderer extends AbstractRenderer
{
    private string $type;

    public function __construct(object|array $subjects, string $type)
    {
        if (!in_array($type, DocumentTypes::getShipmentTypes(), true)) {
            throw new InvalidArgumentException("Unexpected shipment document type '$type'.");
        }

        if ($type === DocumentTypes::TYPE_SHIPMENT_FORM) {
            $subjects = array_filter($subjects, static function (ShipmentInterface $shipment) {
                return !$shipment->isReturn();
            });
        }

        parent::__construct($subjects);

        $this->type = $type;
    }

    public function getFilename(): string
    {
        if (empty($this->subjects)) {
            throw new LogicException('Call addSubject() first.');
        }

        if (1 < count($this->subjects)) {
            return 'shipments';
        }

        /** @var ShipmentInterface $subject */
        $subject = reset($this->subjects);

        return $this->type . '_' . $subject->getNumber();
    }

    protected function getContent(string $format): string
    {
        if ($this->type === DocumentTypes::TYPE_SHIPMENT_FORM) {
            return $this->twig->render('@EkynaCommerce/Document/shipment_form.html.twig', [
                'subjects' => $this->subjects,
            ]);
        }

        return parent::getContent($format);
    }

    protected function getParameters(): array
    {
        return [
            'remaining_date' => $this->config['shipment_remaining_date'],
            'type'           => $this->type,
        ];
    }

    protected function supports(object $subject): bool
    {
        return $subject instanceof ShipmentInterface;
    }

    protected function getTemplate(): string
    {
        return '@EkynaCommerce/Document/shipment.html.twig';
    }
}
