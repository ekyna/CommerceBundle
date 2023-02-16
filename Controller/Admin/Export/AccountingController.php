<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\Export;

use Ekyna\Bundle\CommerceBundle\Service\Export\ExportFormHelper;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Accounting\Export\AccountingExporterInterface;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Resource\Helper\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function is_null;
use function sprintf;

/**
 * Class AccountingController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AccountingController
{
    public function __construct(
        private readonly ExportFormHelper $formHelper,
        private readonly AccountingExporterInterface $accountingExporter,
        private readonly FlashHelper $flashHelper,
        private readonly bool $debug
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $redirect = $this->formHelper->createDashboardRedirect();

        $form = $this->formHelper->createMonthForm('admin_ekyna_commerce_export_accounting');

        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            return $redirect;
        }

        $year = $form->get('year')->getData();
        $month = $form->get('month')->getData();

        if (is_null($month)) {
            // TODO if month is null, schedule background task
        }

        try {
            $path = $this
                ->accountingExporter
                ->export($year, $month);
        } catch (CommerceExceptionInterface $exception) {
            if ($this->debug) {
                throw $exception;
            }

            $this->flashHelper->addFlash($exception->getMessage(), 'danger');

            return $redirect;
        }

        $filename = sprintf('accounting_%s.zip', $year . ($month ? '-' : '') . $month);

        return File::buildResponse($path, [
            'file_name' => $filename,
        ]);
    }
}
