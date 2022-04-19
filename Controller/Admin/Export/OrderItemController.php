<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\Export;

use Ekyna\Bundle\CommerceBundle\Service\Order\OrderItemExporter;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

/**
 * Class OrderItemController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItemController
{
    private OrderItemExporter     $orderItemExporter;
    private UrlGeneratorInterface $urlGenerator;
    private FlashHelper           $flashHelper;
    private bool                  $debug;

    public function __construct(
        OrderItemExporter     $orderItemExporter,
        UrlGeneratorInterface $urlGenerator,
        FlashHelper           $flashHelper,
        bool                  $debug
    ) {
        $this->orderItemExporter = $orderItemExporter;
        $this->urlGenerator = $urlGenerator;
        $this->flashHelper = $flashHelper;
        $this->debug = $debug;
    }

    /**
     * Sample order's items export.
     */
    public function samples(): Response
    {
        try {
            return $this
                ->orderItemExporter
                ->exportSamples()
                ->download();
        } catch (Throwable $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');
        }

        return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
    }
}
