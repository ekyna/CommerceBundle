<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Report;

use Ekyna\Bundle\UiBundle\Form\Type\DateRangeType;
use Ekyna\Component\Commerce\Report\ReportConfig;
use Ekyna\Component\Commerce\Report\ReportRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ReportConfigType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Report
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReportConfigType extends AbstractType
{
    public function __construct(
        private readonly ReportRegistry      $reportRegistry,
        private readonly TranslatorInterface $translator,
        private readonly string              $environment
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => t('field.email_address', [], 'EkynaUi'),
            ])
            ->add('range', DateRangeType::class, [
                'label' => false,
            ])
            ->add('writer', ChoiceType::class, [
                'label'   => t('field.format', [], 'EkynaUi'),
                'choices' => $this->getWriterChoices(),
                'select2' => false,
            ])
            ->add('sections', ChoiceType::class, [
                'label'    => t('field.data', [], 'EkynaUi'),
                'choices'  => $this->getSectionChoices(),
                'multiple' => true,
                'expanded' => true,
            ]);

        if ('dev' === $this->environment) {
            $builder->add('test', CheckboxType::class, [
                'label'    => 'Test mode',
                'required' => false,
            ]);
        }
    }

    public function getWriterChoices(): array
    {
        $choices = [];

        foreach ($this->reportRegistry->getWriters() as $writer) {
            $label = $writer->getTitle()->trans($this->translator);
            $choices[$label] = $writer->getName();
        }

        return $choices;
    }

    public function getSectionChoices(): array
    {
        $choices = [];

        foreach ($this->reportRegistry->getSections() as $section) {
            $label = $section->getTitle()->trans($this->translator);
            $choices[$label] = $section->getName();
        }

        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReportConfig::class,
        ]);
    }
}
