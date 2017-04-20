<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\Export;

use Ekyna\Bundle\CommerceBundle\Form\Type\Accounting\ExportType;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Accounting\Export\AccountingExporterInterface;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Resource\Helper\File\File;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function is_null;
use function sprintf;

/**
 * Class AccountingController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AccountingController
{
    private AccountingExporterInterface $accountingExporter;
    private FormFactoryInterface        $formFactory;
    private UrlGeneratorInterface       $urlGenerator;
    private FlashHelper                 $flashHelper;
    private bool                        $debug;

    public function __construct(
        AccountingExporterInterface $accountingExporter,
        FormFactoryInterface        $formFactory,
        UrlGeneratorInterface       $urlGenerator,
        FlashHelper                 $flashHelper,
        bool                        $debug
    ) {
        $this->accountingExporter = $accountingExporter;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->flashHelper = $flashHelper;
        $this->debug = $debug;
    }

    public function __invoke(Request $request): Response
    {
        $redirect = new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));

        $form = $this->formFactory->create(ExportType::class);

        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            return $redirect;
        }

        $year = $form->get('year')->getData();
        $month = $form->get('month')->getData();

        // TODO if month is null, schedule background task
        if (is_null($month)) {
        }

        try {
            $path = $this
                ->accountingExporter
                ->export($year, $month);
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash($e->getMessage(), 'danger');

            return $redirect;
        }

        $filename = sprintf('accounting_%s.zip', $year . ($month ? '-' : '') . $month);

        return File::buildResponse($path, [
            'file_name' => $filename,
        ]);
    }
}
