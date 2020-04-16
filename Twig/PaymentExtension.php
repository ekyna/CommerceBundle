<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface;
use Ekyna\Bundle\CommerceBundle\Model\PaymentTransitions as BTransitions;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Payment\Calculator\PaymentCalculatorInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentTransitions as CTransitions;
use Ekyna\Component\Commerce\Quote\Model\QuotePaymentInterface;
use Symfony\Component\Translation\TranslatorInterface;
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
     * @var PaymentCalculatorInterface
     */
    private $paymentCalculator;

    /**
     * @var ResourceHelper
     */
    private $resourceHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param PaymentCalculatorInterface $paymentCalculator
     * @param ResourceHelper             $resourceHelper
     * @param TranslatorInterface        $translator
     */
    public function __construct(
        PaymentCalculatorInterface $paymentCalculator,
        ResourceHelper $resourceHelper,
        TranslatorInterface $translator
    ) {
        $this->paymentCalculator = $paymentCalculator;
        $this->resourceHelper = $resourceHelper;
        $this->translator = $translator;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'payment_account_actions',
                [$this, 'getPaymentAccountActions'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'payment_admin_actions',
                [$this, 'getPaymentAdminActions'],
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
                [$this, 'renderPaymentMethodNotice'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'payment_state_message',
                [$this, 'renderPaymentStateMessage'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'payment_method_config',
                [$this, 'renderMethodConfig'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'payment_expected_amount',
                [$this->paymentCalculator, 'calculateExpectedPaymentAmount']
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
                [CTransitions::class, 'isUserCancellable']
            ),
        ];
    }

    /**
     * Returns the available payment account actions.
     *
     * @param PaymentInterface $payment
     *
     * @return string
     */
    public function getPaymentAccountActions(PaymentInterface $payment)
    {
        $transitions = CTransitions::getAvailableTransitions($payment);

        if (empty($transitions)) {
            return '';
        }

        if ($payment instanceof OrderPaymentInterface) {
            $routePrefix = 'ekyna_commerce_account_order_payment_';
        } elseif ($payment instanceof QuotePaymentInterface) {
            $routePrefix = 'ekyna_commerce_account_quote_payment_';
        } else {
            throw new InvalidArgumentException("Unexpected payment.");
        }

        $buttons = [];
        $sale = $payment->getSale();

        foreach ($transitions as $transition) {
            $url = $this->resourceHelper->getUrlGenerator()->generate($routePrefix . $transition, [
                'number' => $sale->getNumber(),
                'key'    => $payment->getKey(),
            ]);

            $buttons[$transition] = [
                'href'    => $url,
                'confirm' => $this->translator->trans(BTransitions::getConfirm($transition)),
            ];
        }

        return $this->renderButtons($buttons);
    }

    /**
     * Returns the available payment admin actions.
     *
     * @param PaymentInterface $payment
     *
     * @return string
     */
    public function getPaymentAdminActions(PaymentInterface $payment)
    {
        $transitions = CTransitions::getAvailableTransitions($payment, true);

        $buttons = [];

        foreach ($transitions as $transition) {
            $url = $this->resourceHelper->generateResourcePath($payment, 'action', [
                'action' => $transition,
            ]);

            $buttons[$transition] = [
                'href'    => $url,
                'confirm' => $this->translator->trans(BTransitions::getConfirm($transition)),
            ];
        }

        /** @var PaymentMethodInterface $method */
        $method = $payment->getMethod();
        if ($method->isManual() || $method->isOutstanding() || $method->isCredit()) {
            $buttons['edit'] = [
                'label' => '<span class="fa fa-pencil"></span>',
                'theme' => 'warning',
                'href'  => $this->resourceHelper->generateResourcePath($payment, 'edit'),
            ];
        }
        if (PaymentStates::isDeletableState($payment->getState())) {
            $buttons['remove'] = [
                'label' => '<span class="fa fa-remove"></span>',
                'theme' => 'danger',
                'href'  => $this->resourceHelper->generateResourcePath($payment, 'remove'),
            ];
        }

        return $this->renderButtons($buttons);
    }

    /**
     * Renders the buttons.
     *
     * @param array $buttons
     *
     * @return string
     */
    private function renderButtons(array $buttons)
    {
        $output = '';

        foreach ($buttons as $transition => $config) {
            if (!isset($config['href'])) {
                throw new InvalidArgumentException("Undefined index 'href'.");
            }

            if (!isset($config['label'])) {
                $config['label'] = $this->translator->trans(BTransitions::getLabel($transition));
            }

            if (!isset($config['theme'])) {
                $config['theme'] = BTransitions::getTheme($transition);
            }

            $confirm = '';
            if (isset($config['confirm'])) {
                $confirm = ' onclick="javascript: return confirm(\'' . $config['confirm'] . '\')"';
            }

            /** @noinspection HtmlUnknownAttribute */
            $output .= sprintf(
                '<a href="%s" class="btn btn-xs btn-%s" %s>%s</a>',
                $config['href'], $config['theme'], $confirm, $config['label']
            );
        }

        return $output;
    }

    /**
     * Renders the payment method config.
     *
     * @param PaymentMethodInterface $method
     *
     * @return string
     */
    public function renderMethodConfig(PaymentMethodInterface $method)
    {
        $output = '<dl class="dl-horizontal">';

        foreach ($method->getConfig() as $key => $value) {
            if (is_array($value)) {
                continue;
            }

            $output .= sprintf('<dt>%s</dt><dd>%s</dd>', $key, $value);
        }

        $output .= '</dl>';

        return $output;
    }

    /**
     * Renders the payment method notice.
     *
     * @param object $subject
     *
     * @return string|null
     */
    public function renderPaymentMethodNotice(object $subject)
    {
        if ($subject instanceof InvoiceInterface) {
            $subject = $subject->getSale();
        }

        if ($subject instanceof SaleInterface) {
            $subject = $subject->getPaymentMethod();
        } elseif ($subject instanceof CustomerInterface) {
            if ($subject->hasParent()) {
                $subject = $subject->getParent();
            }
            $subject = $subject->getDefaultPaymentMethod();
        }

        if (!$subject instanceof PaymentMethodInterface) {
            return null;
        }

        if (empty($notice = $subject->getNotice())) {
            return null;
        }

        return sprintf('<div class="alert alert-warning">%s</div>', $notice);
    }

    /**
     * Renders the payment state message.
     *
     * @param PaymentInterface $payment
     *
     * @return null|string
     */
    public function renderPaymentStateMessage(PaymentInterface $payment)
    {
        $state = $payment->getState();
        $method = $payment->getMethod();

        foreach ($method->getMessages() as $message) {
            if ($message->getState() === $state) {
                $content = $message->getContent();

                return $content;
            }
        }

        return null;
    }
}
