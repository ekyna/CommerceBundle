<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Class ShipmentRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentRenderer extends AbstractRenderer
{
    /**
     * @var string
     */
    private $type;


    /**
     * @inheritdoc
     */
    public function __construct($subjects, $type)
    {
        if (!in_array($type, DocumentTypes::getShipmentTypes(), true)) {
            throw new InvalidArgumentException("Unexpected shipment document type '$type'.");
        }

        if ($type === DocumentTypes::TYPE_SHIPMENT_FORM) {
            $subjects = array_filter($subjects, function (ShipmentInterface $shipment) {
                return !$shipment->isReturn();
            });
        }

        parent::__construct($subjects);

        $this->type = $type;
    }

    /**
     * @inheritdoc
     */
    public function getFilename()
    {
        if (empty($this->subjects)) {
            throw new LogicException("Please add shipment(s) first.");
        }

        if (1 < count($this->subjects)) {
            return 'shipments';
        }

        /** @var ShipmentInterface $subject */
        $subject = reset($this->subjects);

        return 'shipment_' . $this->type . '_' . $subject->getNumber();
    }

    /**
     * @inheritDoc
     */
    protected function getContent(string $format): string
    {
        if ($this->type === DocumentTypes::TYPE_SHIPMENT_FORM) {
            return $this->templating->render('@EkynaCommerce/Document/shipment_form.html.twig', [
                'subjects' => $this->subjects,
            ]);
        }

        return parent::getContent($format);
    }

    /**
     * @inheritdoc
     */
    protected function getParameters()
    {
        return [
            'remaining_date' => $this->config['shipment_remaining_date'],
            'type'           => $this->type,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function supports($subject)
    {
        return $subject instanceof ShipmentInterface;
    }

    /**
     * @inheritdoc
     */
    protected function getTemplate()
    {
        return '@EkynaCommerce/Document/shipment.html.twig';
    }
}
