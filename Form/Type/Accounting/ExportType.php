<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Accounting;

use Ekyna\Component\Commerce\Invoice\Repository\InvoiceRepositoryInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ExportType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Accounting
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExportType extends AbstractType
{
    /**
     * @var array
     */
    private static $yearChoices;

    /**
     * @var array
     */
    private static $monthChoices;

    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param LocaleProviderInterface    $localeProvider
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param TranslatorInterface        $translator
     */
    public function __construct(
        LocaleProviderInterface $localeProvider,
        InvoiceRepositoryInterface $invoiceRepository,
        TranslatorInterface $translator
    ) {
        $this->localeProvider    = $localeProvider;
        $this->invoiceRepository = $invoiceRepository;
        $this->translator        = $translator;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $from = $this->invoiceRepository->findFirstInvoiceDate() ?? new \DateTime();
        $to   = new \DateTime();

        $years = $months = [];

        $period = new \DatePeriod($from, new \DateInterval('P1Y'), $to);
        /** @var \DateTime $date */
        foreach ($period as $date) {
            $years[$date->format('Y')] = $date->format('Y');
        }

        $preferred = new \DateTime('-1 month');

        $builder
            ->add('year', ChoiceType::class, [
                'label'                     => 'ekyna_commerce.accounting.label.plural',
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
                'label'        => 'ekyna_core.button.export',
                'button_class' => 'default',
                'attr'         => [
                    'icon' => 'download',
                ],
            ]);
    }

    /**
     * Returns the years choices.
     *
     * @return array
     */
    private function getYearChoices(): array
    {
        if (self::$yearChoices) {
            return self::$yearChoices;
        }

        $from = $this->invoiceRepository->findFirstInvoiceDate() ?? new \DateTime();
        $to   = new \DateTime();

        $choices = [];
        $period  = new \DatePeriod($from, new \DateInterval('P1Y'), $to);
        /** @var \DateTime $date */
        foreach ($period as $date) {
            $choices[$date->format('Y')] = $date->format('Y');
        }

        return self::$yearChoices = array_reverse($choices, true);
    }

    /**
     * Returns the months choices.
     *
     * @return array
     */
    private function getMonthsChoices(): array
    {
        if (self::$monthChoices) {
            return self::$monthChoices;
        }

        $from = new \DateTime('first day of january');
        $to   = new \DateTime('last day of december');

        $formatter = \IntlDateFormatter::create(
            $this->localeProvider->getCurrentLocale(),
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            $from->getTimezone(),
            \IntlDateFormatter::GREGORIAN,
            'MMMM'
        );

        // TODO Add 'all' choices.
        // For now it consume too much time to be done in a request.
        // It needs to scheduled as background task to be sent by email.
        // $choices = [$this->translator->trans('ekyna_core.value.all') => null];

        $choices = [];
        $period  = new \DatePeriod($from, new \DateInterval('P1M'), $to);
        /** @var \DateTime $date */
        foreach ($period as $date) {
            $key           = mb_convert_case($formatter->format($date->getTimestamp()), MB_CASE_TITLE);
            $choices[$key] = $date->format('m');
        }

        return self::$monthChoices = $choices;
    }
}
