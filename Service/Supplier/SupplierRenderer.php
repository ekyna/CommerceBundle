<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Supplier;

use DateTime;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

use function is_null;
use function sprintf;

/**
 * Class SupplierRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Supplier
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierRenderer
{
    public function __construct(
        private readonly FormatterFactory $formatterFactory,
    ) {
    }

    public function renderPaymentBadge(SupplierOrderInterface $order, array $options = []): string
    {
        $options = array_replace([
            'forwarder' => false,
            'date'      => false,
        ], $options);

        if ($options['forwarder']) {
            $date = $order->getForwarderDueDate();
            $paid = $order->getForwarderPaidTotal();
            $total = $order->getForwarderTotal();
        } else {
            $date = $order->getPaymentDueDate();
            $paid = $order->getPaymentPaidTotal();
            $total = $order->getPaymentTotal();
        }

        $isPast = is_null($date) || (new DateTime())->diff($date)->invert;

        $formatter = $this->formatterFactory->create(currency: $order->getCurrency()->getCode());

        $label = $formatter->currency($paid);

        if ($total->equals($paid)) {
            $theme = 'success';
        } elseif ($isPast) {
            $theme = 'danger';
            if ($options['date']) {
                $label .= ' <em>(' . (is_null($date) ? '?' : $formatter->date($date)) . ')</em>';
            }
        } else {
            $theme = 'warning';
            if ($options['date'] && !is_null($date)) {
                $label .= ' <em>(' . $formatter->date($date) . ')</em>';
            }
        }

        return sprintf('<span class="label label-%s">%s</span>', $theme, $label);
    }
}
