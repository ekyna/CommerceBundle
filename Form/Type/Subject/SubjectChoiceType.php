<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Subject;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class SubjectChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectChoiceType extends AbstractType
{
    /**
     * @var SubjectProviderRegistryInterface
     */
    private $registry;

    /**
     * @var ConfigurationRegistry
     */
    private $configurationRegistry;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * Constructor.
     *
     * @param SubjectProviderRegistryInterface $registry
     * @param ConfigurationRegistry            $configurationRegistry
     * @param UrlGeneratorInterface            $urlGenerator
     */
    public function __construct(
        SubjectProviderRegistryInterface $registry,
        ConfigurationRegistry $configurationRegistry,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->registry = $registry;
        $this->configurationRegistry = $configurationRegistry;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // TODO default choice based on current subject
        // TODO Prevent submit event on subject field (to disable validation of choice list).
        // TODO transformation (provider/identifier  <=>  subject)

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            /** @var SubjectIdentity $identity */
            $identity = $event->getData();

            $disabled = false;
            $subjectChoices = [];
            $subjectRequired = $options['required'];

            if (null !== $identity && $identity->hasIdentity()) {
                $subject = $this->registry
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
                    'label'          => false,
                    'choices'        => $this->getProviderChoices(),
                    'choice_attr'    => $this->getProviderChoiceAttrClosure($options['context']),
                    'select2'        => false,
                    'disabled'       => $disabled,
                    'required'       => $options['required'],
                    'attr'           => [
                        'class' => 'provider',
                    ],
                    'error_bubbling' => true,
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
                    'label'          => false,
                    'choices'        => $subjectChoices,
                    'required'       => $subjectRequired,
                    'disabled'       => true,
                    'select2'        => false,
                    'attr'           => [
                        'class' => 'subject',
                    ],
                    'mapped'         => false,
                    'error_bubbling' => true,
                ]);
        });
    }

    /**
     * Returns the provider choices.
     *
     * @return array
     */
    private function getProviderChoices()
    {
        $choices = [];

        $providers = $this->registry->getProviders();
        foreach ($providers as $provider) {
            $choices[$provider->getLabel()] = $provider->getName();
        }

        return $choices;
    }

    /**
     * Returns the provider choice attr closure.
     *
     * @param string $context
     *
     * @return \Closure
     */
    private function getProviderChoiceAttrClosure($context)
    {
        return function ($val) use ($context) {
            $routing = $this->registry
                ->getProviderByName($val)
                ->getSearchRouteAndParameters($context);

            $routing = array_replace([
                'route'      => null,
                'parameters' => [],
            ], $routing);

            return [
                'data-config' => json_encode([
                    'search' => $this->urlGenerator->generate($routing['route'], $routing['parameters']),
                ])
            ];
        };
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'          => 'ekyna_commerce.subject.label.singular',
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

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_subject_choice';
    }
}
