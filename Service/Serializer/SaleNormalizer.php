<?php

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
    /**
     * @var ConstantsHelper
     */
    private $constantsHelper;

    /**
     * @var FlagRenderer
     */
    private $flagRenderer;

    /**
     * @var TagRenderer
     */
    protected $tagRenderer;


    /**
     * Constructor.
     *
     * @param ConstantsHelper $constantsHelper
     * @param FlagRenderer    $flagRenderer
     */
    public function __construct(ConstantsHelper $constantsHelper, FlagRenderer $flagRenderer)
    {
        $this->constantsHelper = $constantsHelper;
        $this->flagRenderer    = $flagRenderer;
    }

    /**
     * @inheritdoc
     */
    public function normalize($sale, $format = null, array $context = [])
    {
        $data = parent::normalize($sale, $format, $context);

        if ($this->contextHasGroup('Summary', $context)) {
            $data['state_badge'] = $this
                ->constantsHelper
                ->renderSaleStateBadge($sale);

            $data['payment_state_badge'] = $this
                ->constantsHelper
                ->renderPaymentStateBadge($sale->getPaymentState());

            if ($sale instanceof InvoiceSubjectInterface) {
                $data['invoice_state_badge'] = $this
                    ->constantsHelper
                    ->renderInvoiceStateBadge($sale->getInvoiceState());
            }

            if ($sale instanceof ShipmentSubjectInterface) {
                $data['shipment_state_badge'] = $this
                    ->constantsHelper
                    ->renderShipmentStateBadge($sale->getShipmentState());
            }

            $data['flags'] = $this->flagRenderer->renderSaleFlags($sale, ['badge' => false]);

            if ($sale instanceof TagsSubjectInterface) {
                $data['tags'] = $this->getTagRenderer()->renderTags($sale, ['text' => false, 'badge' => false]);
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
        $data =  parent::normalizePayment($payment);

        $data['state_badge'] = $this->constantsHelper->renderPaymentStateBadge($payment);

        return $data;
    }

    /**
     * @inheritDoc
     */
    protected function normalizeShipment(ShipmentInterface $shipment): array
    {
        $data =  parent::normalizeShipment($shipment);

        $data['state_badge'] = $this->constantsHelper->renderShipmentStateBadge($shipment);

        return $data;
    }

    /**
     * Returns the tag renderer.
     *
     * @return TagRenderer
     */
    private function getTagRenderer()
    {
        if ($this->tagRenderer) {
            return $this->tagRenderer;
        }

        return $this->tagRenderer = new TagRenderer();
    }
}
