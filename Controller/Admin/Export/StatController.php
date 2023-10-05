<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\Export;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Service\Stat\StatExporter;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Resource\Helper\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function sprintf;

/**
 * Class StatController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StatController
{
    private StatExporter          $statExporter;
    private UrlGeneratorInterface $urlGenerator;
    private FlashHelper           $flashHelper;
    private bool                  $debug;

    public function __construct(
        StatExporter          $statExporter,
        UrlGeneratorInterface $urlGenerator,
        FlashHelper           $flashHelper,
        bool                  $debug
    ) {
        $this->statExporter = $statExporter;
        $this->urlGenerator = $urlGenerator;
        $this->flashHelper = $flashHelper;
        $this->debug = $debug;
    }

    /**
     * Regions order statistics export.
     *
     * @deprecated
     * @TODO Remove
     */
    public function regionsOrdersStats(): Response
    {
        $from = new DateTime('2019-01-01');
        $to = new DateTime('2019-12-31');

        try {
            $path = $this
                ->statExporter
                ->exportByMonths($from, $to);
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        $filename = sprintf('orders-stats_%s_%s.csv', $from->format('Y-m'), $to->format('Y-m'));

        return File::buildResponse($path, [
            'file_name' => $filename,
        ]);
    }
}
