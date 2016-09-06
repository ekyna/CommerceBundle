<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\CommerceBundle\Model\SubjectChoice;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SubjectChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubjectChoiceType extends AbstractType
{
    /**
     * @var SubjectProviderRegistryInterface
     */
    private $subjectProviderRegistry;


    /**
     * Constructor.
     *
     * @param SubjectProviderRegistryInterface $subjectProviderRegistry
     */
    public function __construct(SubjectProviderRegistryInterface $subjectProviderRegistry)
    {
        $this->subjectProviderRegistry = $subjectProviderRegistry;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch ($options['flow_step']) {
            case 1: // Type (provider) selection
                $types = [];

                foreach ($this->subjectProviderRegistry->getProviders() as $provider) {
                    $types[$provider->getLabel()] = $provider->getName();
                }

                $builder->add('type', ChoiceType::class, array(
                    'label'   => 'Type', // TODO
                    'choices' => $types,
                    'attr' => [
                        'class' => 'no-select2',
                    ]
                ));

                break;

            case 2: // Subject choice selection.
                $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                    /** @var SubjectChoice $subjectChoice */
                    $subjectChoice = $event->getData();
                    $form = $event->getForm();

                    $provider = $this->subjectProviderRegistry->getProvider($subjectChoice->getType());
                    $provider->buildChoiceForm($form);
                });

                break;

            default:
                throw new \RuntimeException('Unexpected flow step.');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SubjectChoice::class
        ]);
    }
}
