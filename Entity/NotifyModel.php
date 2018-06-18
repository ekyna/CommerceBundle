<?php

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Component\Resource\Model\AbstractTranslatable;

/**
 * Class NotifyModel
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method NotifyModelTranslation translate($locale = null, $create = false)
 */
class NotifyModel extends AbstractTranslatable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $paymentMessage;

    /**
     * @var bool
     */
    private $shipmentMessage;

    /**
     * @var string
     */
    private $includeView;

    /**
     * @var array
     */
    private $documentTypes;

    /**
     * @var bool
     */
    private $enabled;


    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->type;
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return NotifyModel
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns whether to include the payment message.
     *
     * @return bool
     */
    public function isPaymentMessage()
    {
        return $this->paymentMessage;
    }

    /**
     * Sets whether to include the payment message.
     *
     * @param bool $include
     *
     * @return NotifyModel
     */
    public function setPaymentMessage($include)
    {
        $this->paymentMessage = $include;

        return $this;
    }

    /**
     * Returns whether to include the shipment message.
     *
     * @return bool
     */
    public function isShipmentMessage()
    {
        return $this->shipmentMessage;
    }

    /**
     * Sets whether to include the shipment message.
     *
     * @param bool $include
     *
     * @return NotifyModel
     */
    public function setShipmentMessage($include)
    {
        $this->shipmentMessage = $include;

        return $this;
    }

    /**
     * Returns the include view.
     *
     * @return string
     */
    public function getIncludeView()
    {
        return $this->includeView;
    }

    /**
     * Sets the include view.
     *
     * @param string $mode
     *
     * @return NotifyModel
     */
    public function setIncludeView($mode)
    {
        $this->includeView = $mode;

        return $this;
    }

    /**
     * Returns the document types.
     *
     * @return array
     */
    public function getDocumentTypes()
    {
        return $this->documentTypes;
    }

    /**
     * Sets the document types.
     *
     * @param array $types
     *
     * @return NotifyModel
     */
    public function setDocumentTypes(array $types)
    {
        $this->documentTypes = $types;

        return $this;
    }

    /**
     * Returns the enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Sets the enabled.
     *
     * @param bool $enabled
     *
     * @return NotifyModel
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool)$enabled;

        return $this;
    }

    /**
     * Returns the translated subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->translate()->getSubject();
    }

    /**
     * Returns the translated message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->translate()->getMessage();
    }
}
