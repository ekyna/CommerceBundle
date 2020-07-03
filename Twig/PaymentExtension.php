<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentRenderer;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Class PaymentExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentExtension extends AbstractExtension
{
    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'payment_account_actions',
                [PaymentRenderer::class, 'renderPaymentAccountActions'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'payment_admin_actions',
                [PaymentRenderer::class, 'renderPaymentAdminActions'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new TwigFilter(
                'payment_state_label',
                [ConstantsHelper::class, 'renderPaymentStateLabel'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'payment_state_badge',
                [ConstantsHelper::class, 'renderPaymentStateBadge'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'payment_term_trigger_label',
                [ConstantsHelper::class, 'renderPaymentTermTriggerLabel'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'payment_method_notice',
                [PaymentRenderer::class, 'renderPaymentMethodNotice'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'payment_state_message',
                [PaymentRenderer::class, 'renderPaymentStateMessage'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'payment_method_config',
                [PaymentRenderer::class, 'renderMethodConfig'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'payment_expected_amount',
                [PaymentRenderer::class, 'getExpectedPaymentAmount']
            ),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTests()
    {
        return [
            new TwigTest('payment', function ($subject) {
                return $subject instanceof PaymentInterface;
            }),
            new TwigTest('payment_subject', function ($subject) {
                return $subject instanceof PaymentSubjectInterface;
            }),
            new TwigTest(
                'payment_user_cancellable',
                [PaymentRenderer::class, 'isUserCancellable']
            ),
        ];
    }
}
