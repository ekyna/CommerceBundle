<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SubjectProviderType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubjectProviderType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var SubjectProviderRegistryInterface $registry */
        $registry = $options['provider_registry'];

        $types = [];
        foreach ($registry->getProviders() as $provider) {
            $types[$provider->getLabel()] = $provider->getName();
        }

        $builder->add('provider', ChoiceType::class, array(
            'label'   => 'Type', // TODO
            'choices' => $types,
            'property_path' => 'subjectData[provider]',
            'attr' => [
                'class' => 'no-select2',
            ],
        ));
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'provider_registry' => null,
                'data_class' => SaleItemInterface::class
            ])
            ->setAllowedTypes('provider_registry', SubjectProviderRegistryInterface::class);
    }
}
