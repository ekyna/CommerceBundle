<?php

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface;
use Ekyna\Bundle\MediaBundle\Model\MediaSubjectTrait;
use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Constants as Credit;
use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants as Offline;
use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants as Outstanding;
use Ekyna\Component\Commerce\Payment\Entity\PaymentMethod as BaseMethod;

/**
 * Class PaymentMethod
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethod extends BaseMethod implements PaymentMethodInterface
{
    use MediaSubjectTrait;

    /**
     * @var string
     */
    protected $gatewayName;

    /**
     * @var string
     */
    protected $factoryName;

    /**
     * @var array
     */
    protected $config;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->config = [];
    }

    /**
     * @inheritdoc
     */
    public function getGatewayName()
    {
        return $this->gatewayName;
    }

    /**
     * @inheritdoc
     *
     * @return $this|PaymentMethodInterface
     */
    public function setGatewayName($gatewayName)
    {
        $this->gatewayName = $gatewayName;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFactoryName()
    {
        return $this->factoryName;
    }

    /**
     * @inheritdoc
     *
     * @return $this|PaymentMethodInterface
     */
    public function setFactoryName($factoryName)
    {
        $this->factoryName = $factoryName;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return array_replace($this->config, [
            'factory' => $this->factoryName,
        ]);
    }

    /**
     * @inheritdoc
     *
     * @return $this|PaymentMethodInterface
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isManual(): bool
    {
        return $this->getFactoryName() === Offline::FACTORY_NAME;
    }

    /**
     * @inheritdoc
     */
    public function isCredit(): bool
    {
        return $this->getFactoryName() === Credit::FACTORY_NAME;
    }

    /**
     * @inheritdoc
     */
    public function isOutstanding(): bool
    {
        return $this->getFactoryName() === Outstanding::FACTORY_NAME;
    }
}
