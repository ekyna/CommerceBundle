<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface;
use Ekyna\Bundle\CommerceBundle\Model\PaymentTransitions;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Util\PaymentUtil;
use Ekyna\Component\Commerce\Quote\Model\QuotePaymentInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class PaymentExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentExtension extends \Twig_Extension
{
    /**
     * @var ConstantsHelper
     */
    private $constantHelper;

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
     * @param ConstantsHelper     $constantHelper
     * @param ResourceHelper      $resourceHelper
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ConstantsHelper $constantHelper,
        ResourceHelper $resourceHelper,
        TranslatorInterface $translator
    ) {
        $this->constantHelper = $constantHelper;
        $this->resourceHelper = $resourceHelper;
        $this->translator = $translator;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'payment_account_actions',
                [$this, 'getPaymentAccountActions'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
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
            new \Twig_SimpleFilter(
                'payment_state_label',
                [$this->constantHelper, 'renderPaymentStateLabel'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'payment_state_badge',
                [$this->constantHelper, 'renderPaymentStateBadge'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'payment_state_message',
                [$this, 'renderPaymentStateMessage'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'payment_method_config',
                [$this, 'renderMethodConfig'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTests()
    {
        return [
            new \Twig_SimpleTest(
                'payment_user_cancellable',
                [PaymentUtil::class, 'isUserCancellable']
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
        $transitions = PaymentTransitions::getAvailableTransitions($payment);

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
                'confirm' => $this->translator->trans(PaymentTransitions::getConfirm($transition)),
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
        $transitions = PaymentTransitions::getAvailableTransitions($payment, true);

        $buttons = [];

        foreach ($transitions as $transition) {
            $url = $this->resourceHelper->generateResourcePath($payment, 'action', [
                'action' => $transition,
            ]);

            $buttons[$transition] = [
                'href'    => $url,
                'confirm' => $this->translator->trans(PaymentTransitions::getConfirm($transition)),
            ];
        }

        /** @var PaymentMethodInterface $method */
        $method = $payment->getMethod();
        if ($method->isManual()) {
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
                $config['label'] = $this->translator->trans(PaymentTransitions::getLabel($transition));
            }

            if (!isset($config['theme'])) {
                $config['theme'] = PaymentTransitions::getTheme($transition);
            }

            $confirm = '';
            if (isset($config['confirm'])) {
                $confirm = ' onclick="javascript: return confirm(\'' . $config['confirm'] . '\')"';
            }

            $output .= sprintf(
                '<a href="%s" class="btn btn-xs btn-%s"%s>%s</a>',
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

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_payment';
    }
}
