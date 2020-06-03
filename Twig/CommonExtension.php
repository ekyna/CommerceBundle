<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Common\CommonRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Common\ButtonRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Common\FlagRenderer;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Common\Currency\CurrencyRenderer;
use Ekyna\Component\Commerce\Features;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class CommonExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CommonExtension extends AbstractExtension
{
    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new TwigFilter(
                'address',
                [CommonRenderer::class, 'renderAddress'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'customer_contact',
                [CommonRenderer::class, 'renderCustomerContact'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'identity',
                [ConstantsHelper::class, 'renderIdentity'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'gender',
                [ConstantsHelper::class, 'getGenderLabel']
            ),
            new TwigFilter(
                'adjustment_mode_label',
                [ConstantsHelper::class, 'getAdjustmentModeLabel']
            ),
            new TwigFilter(
                'adjustment_type_label',
                [ConstantsHelper::class, 'getAdjustmentTypeLabel']
            ),
            new TwigFilter(
                'accounting_type_label',
                [ConstantsHelper::class, 'renderAccountingTypeLabel']
            ),
            new TwigFilter(
                'customer_state_label',
                [ConstantsHelper::class, 'renderCustomerStateLabel']
            ),
            new TwigFilter(
                'customer_state_badge',
                [ConstantsHelper::class, 'renderCustomerStateBadge'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'notify_type_label',
                [ConstantsHelper::class, 'renderNotifyTypeLabel']
            ),
            new TwigFilter(
                'sale_flags',
                [FlagRenderer::class, 'renderSaleFlags'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'currency_quote',
                [CurrencyRenderer::class, 'renderQuote'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'currency_base',
                [CurrencyRenderer::class, 'renderBase'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'currency_rate',
                [CurrencyRenderer::class, 'renderRate'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'sale_custom_buttons',
                [ButtonRenderer::class, 'renderSaleCustomButtons'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'currency_configure',
                [CurrencyRenderer::class, 'configure']
            ),
            new TwigFunction(
                'currency_get_base',
                [CurrencyRenderer::class, 'getBase']
            ),
            new TwigFunction(
                'currency_get_quote',
                [CurrencyRenderer::class, 'getQuote']
            ),
            new TwigFunction(
                'currency_rate',
                [CurrencyRenderer::class, 'renderRate'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'commerce_feature',
                [Features::class, 'isEnabled']
            ),
        ];
    }
}
