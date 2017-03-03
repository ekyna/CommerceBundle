<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Subject;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
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

            $subjectChoices = [];
            $subjectRequired = $options['required'];

            /** @noinspection PhpInternalEntityUsedInspection */
            if ($identity->hasIdentity()) {
                $subject = $this->registry
                    ->getProviderByName($identity->getProvider())
                    ->reverseTransform($identity);

                $subjectChoices[(string)$subject] = $subject;
                $subjectRequired = true;

                //$event->setData($identity);
            }

            $disabled = $options['lock_mode'] && $identity->hasIdentity();

            $form
                ->add('provider', ChoiceType::class, [
                    'label'       => false,
                    'choices'     => $this->getProviderChoices(),
                    'choice_attr' => $this->getProviderChoiceAttrClosure(),
                    'select2'     => false,
                    'disabled'    => $disabled,
                    'required'    => $options['required'],
                    'attr'        => [
                        'class' => 'provider',
                    ],
                ])
                ->add('identifier', HiddenType::class, [
                    'disabled' => $disabled,
                    'required' => $options['required'],
                    'attr'     => [
                        'class' => 'identifier',
                    ],
                ])
                ->add('subject', ChoiceType::class, [
                    'label'    => false,
                    'choices'  => $subjectChoices,
                    'required' => $subjectRequired,
                    'disabled' => true,
                    'select2'  => false,
                    'attr'     => [
                        'class' => 'subject',
                    ],
                    'mapped'   => false,
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
     * @return \Closure
     */
    private function getProviderChoiceAttrClosure()
    {
        return function ($val) {
            $config = [];

            $mapping = [
                'product'      => 'ekyna',
                'acme_product' => 'acme',
            ];

            if (isset($mapping[$val])) {
                // TODO Use event dispatcher to get this config.
                // TODO Product : search only for Simple/Variant
                $config = [
                    'search' => $this->urlGenerator->generate($mapping[$val] . '_product_product_admin_search'),
                    'find'   => $this->urlGenerator->generate($mapping[$val] . '_product_product_admin_find'),
                ];
            }

            return ['data-config' => json_encode($config)];
        };
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'      => 'subject',
                'lock_mode'  => false,
                'data_class' => SubjectIdentity::class,
            ])
            ->setAllowedTypes('lock_mode', 'bool');
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_subject_choice';
    }
}
