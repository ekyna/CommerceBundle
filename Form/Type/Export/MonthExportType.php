<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Export;

use DateInterval;
use DatePeriod;
use DateTime;
use Ekyna\Component\Commerce\Invoice\Repository\InvoiceRepositoryInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use IntlDateFormatter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

use function array_reverse;
use function mb_convert_case;
use function Symfony\Component\Translation\t;

use const MB_CASE_TITLE;

/**
 * Class MonthExportType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Accounting
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MonthExportType extends AbstractType
{
    private static ?array $yearChoices = null;
    private static ?array $monthChoices = null;

    private LocaleProviderInterface    $localeProvider;
    private InvoiceRepositoryInterface $invoiceRepository;

    public function __construct(
        LocaleProviderInterface    $localeProvider,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->localeProvider = $localeProvider;
        $this->invoiceRepository = $invoiceRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $preferred = new DateTime('-1 month');

        $builder
            ->add('year', ChoiceType::class, [
                'label'                     => false,
                'choices'                   => $this->getYearChoices(),
                'select2'                   => false,
                'choice_translation_domain' => false,
                'preferred_choices'         => [$preferred->format('Y')],
            ])
            ->add('month', ChoiceType::class, [
                'label'                     => false,
                'choices'                   => $this->getMonthsChoices(),
                'select2'                   => false,
                'choice_translation_domain' => false,
                'preferred_choices'         => [$preferred->format('m')],
            ])
            ->add('submit', SubmitType::class, [
                'label'        => t('button.export', [], 'EkynaUi'),
                'button_class' => 'default',
                'attr'         => [
                    'icon' => 'download',
                ],
            ]);
    }

    private function getYearChoices(): array
    {
        if (self::$yearChoices) {
            return self::$yearChoices;
        }

        $from = $this->invoiceRepository->findFirstInvoiceDate() ?? new DateTime();
        $from->modify('first day of january');
        $to = new DateTime();

        $choices = [];
        $period = new DatePeriod($from, new DateInterval('P1Y'), $to);
        /** @var DateTime $date */
        foreach ($period as $date) {
            $choices[$date->format('Y')] = $date->format('Y');
        }

        return self::$yearChoices = array_reverse($choices, true);
    }

    private function getMonthsChoices(): array
    {
        if (self::$monthChoices) {
            return self::$monthChoices;
        }

        $from = new DateTime('first day of january');
        $to = new DateTime('last day of december');

        // TODO Use formatter factory
        $formatter = IntlDateFormatter::create(
            $this->localeProvider->getCurrentLocale(),
            IntlDateFormatter::SHORT,
            IntlDateFormatter::NONE,
            $from->getTimezone(),
            IntlDateFormatter::GREGORIAN,
            'MMMM'
        );

        // TODO Add 'all' choices.
        // For now it consume too much time to be done in a request.
        // It needs to scheduled as background task to be sent by email.
        // $choices = [$this->translator->trans('value.all', [], 'EkynaUi') => null];

        $choices = [];
        $period = new DatePeriod($from, new DateInterval('P1M'), $to);
        /** @var DateTime $date */
        foreach ($period as $date) {
            $key = mb_convert_case($formatter->format($date->getTimestamp()), MB_CASE_TITLE);
            $choices[$key] = $date->format('m');
        }

        return self::$monthChoices = $choices;
    }
}
