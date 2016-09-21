<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

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
            new \Twig_SimpleFilter('payment_state_label', [$this->constantHelper, 'renderPaymentStateLabel'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('payment_state_badge', [$this->constantHelper, 'renderPaymentStateBadge'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_payment';
    }
}
