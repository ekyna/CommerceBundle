<?php

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface;
use Ekyna\Bundle\MediaBundle\Model\MediaSubjectTrait;
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
     * @see \Ekyna\Component\Commerce\Common\Model\MethodInterface::getName
     */
    public function getGatewayName()
    {
        return $this->getName();
    }

    /**
     * @inheritdoc
     * @see \Ekyna\Component\Commerce\Common\Model\MethodInterface::setName
     * @return $this|PaymentMethodInterface
     */
    public function setGatewayName($gatewayName)
    {
        return $this->setName($gatewayName);
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
        return $this->config;
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
}
