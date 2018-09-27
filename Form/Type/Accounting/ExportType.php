<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Accounting;

use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ExportType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Accounting
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExportType extends AbstractType
{
    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider;


    /**
     * Constructor.
     *
     * @param LocaleProviderInterface $localeProvider
     */
    public function __construct(LocaleProviderInterface $localeProvider)
    {
        $this->localeProvider = $localeProvider;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];

        $date = new \DateTime();
        $date->modify('first day of this month');

        $formatter = \IntlDateFormatter::create(
            $this->localeProvider->getCurrentLocale(),
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            $date->getTimezone(),
            \IntlDateFormatter::GREGORIAN,
            'MMMM Y'
        );

        for ($i = 12; $i > 0; $i--) {
            $date->modify('-1 month');

            $value = $date->format('Y-m-d');
            $key = mb_convert_case($formatter->format($date->getTimestamp()), MB_CASE_TITLE);

            $choices[$key] = $value;
        }

        $builder
            ->add('date', ChoiceType::class, [
                'label'   => 'ekyna_commerce.dashboard.export.accounting',
                'choices' => $choices,
                'select2' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label'        => 'ekyna_core.button.export',
                'button_class' => 'default',
                'attr'         => [
                    'icon' => 'download',
                ],
            ]);
    }
}
