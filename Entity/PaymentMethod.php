<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface;
use Ekyna\Bundle\MediaBundle\Model\MediaSubjectTrait;
use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Constants as Credit;
use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants as Offline;
use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants as Outstanding;
use Ekyna\Component\Commerce\Payment\Entity\PaymentMethod as BaseMethod;
use Ekyna\Component\Commerce\Payment\Entity\PaymentMethodTranslation;

/**
 * Class PaymentMethod
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method PaymentMethodTranslation translate($locale = null, $create = false)
 * @method ArrayCollection|PaymentMethodTranslation[] getTranslations()
 */
class PaymentMethod extends BaseMethod implements PaymentMethodInterface
{
    use MediaSubjectTrait;

    protected ?string $gatewayName = null;
    protected ?string $factoryName = null;
    protected array   $config = [];


    /**
     * @inheritDoc
     */
    public function getGatewayName(): ?string
    {
        return $this->gatewayName;
    }

    /**
     * @inheritDoc
     *
     * @return $this|PaymentMethodInterface
     */
    public function setGatewayName($gatewayName): PaymentMethodInterface
    {
        $this->gatewayName = $gatewayName;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFactoryName(): ?string
    {
        return $this->factoryName;
    }

    /**
     * @inheritDoc
     *
     * @return $this|PaymentMethodInterface
     */
    public function setFactoryName($name): PaymentMethodInterface
    {
        $this->factoryName = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        return array_replace($this->config, [
            'factory' => $this->factoryName,
        ]);
    }

    /**
     * @inheritDoc
     *
     * @return $this|PaymentMethodInterface
     */
    public function setConfig(array $config): PaymentMethodInterface
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
     * @inheritDoc
     */
    public function isCredit(): bool
    {
        return $this->getFactoryName() === Credit::FACTORY_NAME;
    }

    /**
     * @inheritDoc
     */
    public function isOutstanding(): bool
    {
        return $this->getFactoryName() === Outstanding::FACTORY_NAME;
    }

    /**
     * @inheritDoc
     */
    public function isFactor(): bool
    {
        return $this->isManual() && isset($this->config['factor']) && $this->config['factor'];
    }
}
