<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\CommerceBundle\Model\SubjectChoice;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SubjectConfigurationType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubjectConfigurationType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var SubjectProviderRegistryInterface $registry */
        $registry = $options['provider_registry'];

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($registry) {
                /** @var SaleItemInterface $item */
                $item = $event->getData();
                $form = $event->getForm();

                $registry
                    ->getProvider($item)
                    ->buildConfigurationForm($form, $item);
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($registry) {
                /** @var SaleItemInterface $item */
                $item = $event->getData();

                $registry
                    ->getProvider($item)
                    ->handleConfigurationSubmit($item);
            });
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
