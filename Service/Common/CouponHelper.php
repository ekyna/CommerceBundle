<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Helper\CouponHelper as BaseHelper;
use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Commerce\Common\Repository\CouponRepositoryInterface;
use Ekyna\Component\Commerce\Exception\CouponException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CouponHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CouponHelper extends BaseHelper
{
    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param CouponRepositoryInterface $repository
     * @param AmountCalculatorFactory   $factory
     * @param TranslatorInterface       $translator
     * @param string                    $currency
     */
    public function __construct(
        CouponRepositoryInterface $repository,
        AmountCalculatorFactory $factory,
        TranslatorInterface $translator,
        string $currency
    ) {
        parent::__construct($repository, $factory, $currency);

        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    protected function getDesignation(CouponInterface $coupon): string
    {
        return $coupon->getDesignation()
            ?? $this->translator->trans('ekyna_commerce.coupon.message.designation', [
                '%code%' => $coupon->getCode(),
            ]);
    }

    /**
     * @inheritDoc
     */
    protected function createException(string $message, array $parameters = []): CouponException
    {
        return new CouponException(
            $this->translator->trans('ekyna_commerce.coupon.message.' . $message, $parameters)
        );
    }
}
