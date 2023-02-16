<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Export;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Form\Type\Export\MonthExportType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Export\RangeExportType;
use Ekyna\Component\Resource\Model\DateRange;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ExportHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ExportFormHelper
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function createMonthForm(string $submitRoute): FormInterface
    {
        return $this->formFactory->create(MonthExportType::class, null, [
            'action' => $this->urlGenerator->generate($submitRoute),
            'method' => 'POST',
        ]);
    }

    public function createRangeForm(string $submitRoute): FormInterface
    {
        $range = new DateRange(
            (new DateTime())->modify('first day of january')->setTime(0, 0),
            new DateTime()
        );

        return $this->formFactory->create(RangeExportType::class, $range, [
            'action' => $this->urlGenerator->generate($submitRoute),
            'method' => 'POST',
        ]);
    }

    public function createDashboardRedirect(): Response
    {
        return new RedirectResponse(
            $this->urlGenerator->generate('admin_dashboard')
        );
    }
}
