<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Bundle\CmsBundle\Model\TagsSubjectInterface;
use Ekyna\Bundle\CmsBundle\Service\Renderer\TagRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Common\FlagRenderer;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\SaleNormalizer as BaseNormalizer;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;

/**
 * Class SaleNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleNormalizer extends BaseNormalizer
{
    private ConstantsHelper $constantsHelper;
    private FlagRenderer    $flagRenderer;

    protected ?TagRenderer $tagRenderer = null;

    public function __construct(ConstantsHelper $constantsHelper, FlagRenderer $flagRenderer)
    {
        $this->constantsHelper = $constantsHelper;
        $this->flagRenderer = $flagRenderer;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if (self::contextHasGroup('Summary', $context)) {
            $data['state_badge'] = $this
                ->constantsHelper
                ->renderSaleStateBadge($object);

            $data['payment_state_badge'] = $this
                ->constantsHelper
                ->renderPaymentStateBadge($object->getPaymentState());

            if ($object instanceof InvoiceSubjectInterface) {
                $data['invoice_state_badge'] = $this
                    ->constantsHelper
                    ->renderInvoiceStateBadge($object->getInvoiceState());
            }

            if ($object instanceof ShipmentSubjectInterface) {
                $data['shipment_state_badge'] = $this
                    ->constantsHelper
                    ->renderShipmentStateBadge($object->getShipmentState());
            }

            $data['flags'] = $this->flagRenderer->renderSaleFlags($object, ['badge' => false]);

            if ($object instanceof TagsSubjectInterface) {
                $data['tags'] = $this->getTagRenderer()->renderTags($object, ['text' => false, 'badge' => false]);
            } else {
                $data['tags'] = '';
            }
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    protected function normalizePayment(PaymentInterface $payment): array
    {
        $data = parent::normalizePayment($payment);

        $data['state_badge'] = $this->constantsHelper->renderPaymentStateBadge($payment);

        return $data;
    }

    /**
     * @inheritDoc
     */
    protected function normalizeShipment(ShipmentInterface $shipment): array
    {
        $data = parent::normalizeShipment($shipment);

        $data['state_badge'] = $this->constantsHelper->renderShipmentStateBadge($shipment);

        return $data;
    }

    /**
     * Returns the tag renderer.
     *
     * @return TagRenderer
     */
    private function getTagRenderer(): TagRenderer
    {
        if ($this->tagRenderer) {
            return $this->tagRenderer;
        }

        return $this->tagRenderer = new TagRenderer();
    }
}
