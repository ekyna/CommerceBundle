<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Subject;

use Closure;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\Translation\t;

/**
 * Class SubjectChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectChoiceType extends AbstractType
{
    private SubjectProviderRegistryInterface $providerRegistry;
    private ResourceHelper                   $resourceHelper;
    private TranslatorInterface              $translator;


    public function __construct(
        SubjectProviderRegistryInterface $registry,
        ResourceHelper                   $resourceHelper,
        TranslatorInterface              $translator
    ) {
        $this->providerRegistry = $registry;
        $this->resourceHelper = $resourceHelper;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // TODO default choice based on current subject
        // TODO Prevent submit event on subject field (to disable validation of choice list).
        // TODO transformation (provider/identifier  <=>  subject)

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
            $form = $event->getForm();
            /** @var SubjectIdentity $identity */
            $identity = $event->getData();

            $disabled = false;
            $subjectChoices = [];
            $subjectRequired = $options['required'];

            if (null !== $identity && $identity->hasIdentity()) {
                $subject = $this->providerRegistry
                    ->getProviderByName($identity->getProvider())
                    ->reverseTransform($identity);

                $subjectChoices[(string)$subject] = $subject->getId();
                $subjectRequired = true;

                if ($options['lock_mode']) {
                    $disabled = true;
                }
            }

            $form
                ->add('provider', ChoiceType::class, [
                    'label'                     => false,
                    'choices'                   => $this->getProviderChoices(),
                    'choice_translation_domain' => false,
                    'choice_attr'               => $this->getProviderChoiceAttrClosure($options['context']),
                    'select2'                   => false,
                    'disabled'                  => $disabled,
                    'required'                  => $options['required'],
                    'attr'                      => [
                        'class' => 'provider',
                    ],
                    'error_bubbling'            => true,
                ])
                ->add('identifier', HiddenType::class, [
                    'disabled'       => $disabled,
                    'required'       => $options['required'],
                    'attr'           => [
                        'class' => 'identifier',
                    ],
                    'error_bubbling' => true,
                ])
                ->add('subject', ChoiceType::class, [
                    'label'                     => false,
                    'choices'                   => $subjectChoices,
                    'choice_translation_domain' => false,
                    'required'                  => $subjectRequired,
                    'disabled'                  => true,
                    'select2'                   => false,
                    'attr'                      => [
                        'class' => 'subject',
                    ],
                    'mapped'                    => false,
                    'error_bubbling'            => true,
                ]);
        });
    }

    private function getProviderChoices(): array
    {
        $choices = [];

        $providers = $this->providerRegistry->getProviders();
        foreach ($providers as $provider) {
            $label = $provider->getLabel();

            if ($label instanceof TranslatableInterface) {
                $label = $label->trans($this->translator);
            }

            $choices[$label] = $provider->getName();
        }

        return $choices;
    }

    /**
     * Returns the provider choice attr closure.
     */
    private function getProviderChoiceAttrClosure(string $context): Closure
    {
        return function ($val) use ($context) {
            $provider = $this->providerRegistry->getProviderByName($val);

            $values = $provider->getSearchActionAndParameters($context);

            $values = array_replace([
                'route'      => null,
                'action'     => null,
                'parameters' => [],
            ], $values);

            if ($values['action']) {
                $path = $this
                    ->resourceHelper
                    ->generateResourcePath($provider->getSubjectClass(), $values['action'], $values['parameters']);
            } elseif ($values['route']) {
                $path = $this
                    ->resourceHelper
                    ->getUrlGenerator()
                    ->generate($values['route'], $values['parameters']);
            } else {
                throw new LogicException('Neither \'route\' nor \'action\' is defined.');
            }

            return [
                'data-config' => json_encode([
                    'search' => $path,
                ]),
            ];
        };
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label'          => t('subject.label.singular', [], 'EkynaCommerce'),
                'lock_mode'      => false,
                'data_class'     => SubjectIdentity::class,
                'error_bubbling' => false,
                'context'        => null,
            ])
            ->setAllowedTypes('lock_mode', 'bool')
            ->setAllowedTypes('context', 'string')
            ->setAllowedValues('context', [
                SubjectProviderInterface::CONTEXT_ITEM,
                SubjectProviderInterface::CONTEXT_SALE,
                SubjectProviderInterface::CONTEXT_ACCOUNT,
                SubjectProviderInterface::CONTEXT_SUPPLIER,
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_subject_choice';
    }
}
