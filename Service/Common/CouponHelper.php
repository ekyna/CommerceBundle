<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleCouponType;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Helper\CouponHelper as BaseHelper;
use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Commerce\Common\Repository\CouponRepositoryInterface;
use Ekyna\Component\Commerce\Exception\CouponException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class CouponHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CouponHelper extends BaseHelper
{
    private FormFactoryInterface $formFactory;
    private UrlGeneratorInterface $urlGenerator;
    private TranslatorInterface $translator;

    public function __construct(
        CouponRepositoryInterface $repository,
        AmountCalculatorFactory $factory,
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator,
        string $currency
    ) {
        parent::__construct($repository, $factory, $currency);

        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }

    public function createCartCouponForm(CartInterface $cart): FormInterface
    {
        if ($coupon = $cart->getCoupon()) {
            $action = $this->urlGenerator->generate('ekyna_commerce_cart_coupon_clear');
            $code = $coupon->getCode();
        } else {
            $action = $this->urlGenerator->generate('ekyna_commerce_cart_coupon_set');
            $code = null;
        }

        return $this->formFactory->create(SaleCouponType::class, null, [
            'method' => 'post',
            'action' => $action,
            'code'   => $code,
        ]);
    }

    protected function getDesignation(CouponInterface $coupon): string
    {
        return $coupon->getDesignation()
            ?? $this->translator->trans('coupon.message.designation', [
                '%code%' => $coupon->getCode(),
            ], 'EkynaCommerce');
    }

    protected function createException(string $message, array $parameters = []): CouponException
    {
        return new CouponException(
            $this->translator->trans('coupon.message.' . $message, $parameters, 'EkynaCommerce')
        );
    }
}
