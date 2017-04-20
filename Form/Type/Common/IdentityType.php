<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Component\Commerce\Common\Model\IdentityInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class IdentityType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class IdentityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $section = $options['section'] ? 'section-' . $options['section'] . ' ' : '';

        $builder
            ->add('gender', GenderChoiceType::class, [
                'label'          => false,
                'expanded'       => false,
                'required'       => $options['required'],
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'identity-gender',
                ],
            ])
            ->add('lastName', TextType::class, [
                'label'              => false,
                'required'           => $options['required'],
                'attr'               => [
                    'class'        => 'identity-last-name',
                    'placeholder'  => t('field.last_name', [], 'EkynaUi'),
                    'maxlength'    => 35,
                    'autocomplete' => $section . 'given-name',
                ],
                'error_bubbling'     => true,
            ])
            ->add('firstName', TextType::class, [
                'label'              => false,
                'required'           => $options['required'],
                'attr'               => [
                    'class'        => 'identity-first-name',
                    'placeholder'  => t('field.first_name', [], 'EkynaUi'),
                    'maxlength'    => 35,
                    'autocomplete' => $section . 'family-name',
                ],
                'error_bubbling'     => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class'         => IdentityInterface::class,
                'label'              => t('field.identity', [], 'EkynaUi'),
                'inherit_data'       => true,
                'required'           => true,
                'error_bubbling'     => false,
                'section'            => null,
            ])
            ->setAllowedTypes('section', ['string', 'null']);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_identity';
    }
}
