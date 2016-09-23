<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface;
use Ekyna\Bundle\CommerceBundle\Service\ConstantHelper;

/**
 * Class PaymentExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentExtension extends \Twig_Extension
{
    /**
     * @var ConstantHelper
     */
    private $constantHelper;


    /**
     * Constructor.
     *
     * @param \Ekyna\Bundle\CommerceBundle\Service\ConstantHelper $constantHelper
     */
    public function __construct(ConstantHelper $constantHelper)
    {
        $this->constantHelper = $constantHelper;
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
                'payment_method_config',
                [$this, 'renderMethodConfig'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Renders the payment method config.
     *
     * @param PaymentMethodInterface $method
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
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_payment';
    }
}
