<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
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
     * @param AmountCalculatorInterface $calculator
     * @param TranslatorInterface       $translator
     */
    public function __construct(
        CouponRepositoryInterface $repository,
        AmountCalculatorInterface $calculator,
        TranslatorInterface $translator
    ) {
        parent::__construct($repository, $calculator);

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
