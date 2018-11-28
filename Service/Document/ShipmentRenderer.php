<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

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
    const TYPE_BILL = 'bill';
    const TYPE_FORM = 'form';

    /**
     * @var string
     */
    private $type;


    /**
     * @inheritDoc
     */
    public function __construct($subjects, $type)
    {
        if (!in_array($type, [static::TYPE_BILL, static::TYPE_FORM], true)) {
            throw new InvalidArgumentException("Unexpected shipment document type '$type'.");
        }

        if ($type === static::TYPE_FORM) {
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
     * Returns the document's content.
     *
     * @return string
     */
    protected function getContent()
    {
        if ($this->type === static::TYPE_FORM) {
            return $this->templating->render('@EkynaCommerce/Document/shipment_form.html.twig', [
                'subjects' => $this->subjects,
            ]);
        }

        return parent::getContent();
    }

    /**
     * @inheritDoc
     */
    protected function getParameters()
    {
        return [
            'type' => $this->type,
        ];
    }

    /**
     * @inheritDoc
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
