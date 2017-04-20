<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\MapConfig;
use Ekyna\Bundle\CommerceBundle\Service\Map\MapBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class MapType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MapType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mode', ChoiceType::class, [
                'label'                     => t('field.mode', [], 'EkynaUi'),
                'choices'                   => MapBuilder::getModeChoices(),
                'choice_translation_domain' => 'EkynaCommerce',
            ])
            ->add('groups', CustomerGroupChoiceType::class, [
                'required' => false,
                'multiple' => true,
            ])
            ->add('search', TextType::class, [
                'label'    => t('field.search', [], 'EkynaUi'),
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', MapConfig::class);
    }
}
