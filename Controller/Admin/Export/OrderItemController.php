<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\Export;

use Ekyna\Bundle\CommerceBundle\Service\Export\ExportFormHelper;
use Ekyna\Bundle\CommerceBundle\Service\Order\OrderItemExporter;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Model\DateRange;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Class OrderItemController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItemController
{
    public function __construct(
        private readonly ExportFormHelper $formHelper,
        private readonly OrderItemExporter $orderItemExporter,
        private readonly FlashHelper $flashHelper,
        private readonly bool $debug
    ) {
    }

    /**
     * Sample order's items export.
     */
    public function __invoke(Request $request): Response
    {
        $redirect = $this->formHelper->createDashboardRedirect();

        $form = $this->formHelper->createRangeForm('admin_ekyna_commerce_export_sample_order_items');

        $form->handleRequest($request);
        if (!($form->isSubmitted() && $form->isValid())) {
            return $redirect;
        }

        $range = $form->getData();
        if (!$range instanceof DateRange) {
            throw new UnexpectedTypeException($range, DateRange::class);
        }

        $start = $form->get('start')->getData();
        $end = $form->get('end')->getData();

        try {
            $file = $this
                ->orderItemExporter
                ->exportSamples($start, $end);
        } catch (Throwable $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return $redirect;
        }

        return $file->download();
    }
}
