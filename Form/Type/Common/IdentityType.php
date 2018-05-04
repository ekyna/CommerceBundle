<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class IdentityType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class IdentityType extends AbstractType
{
    /**
     * @var string
     */
    private $gendersClass;


    /**
     * Constructor.
     *
     * @param string $gendersClass
     */
    public function __construct($gendersClass)
    {
        $this->gendersClass = $gendersClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $section = $options['section'] ? 'section-' . $options['section'] . ' ' : '';

        $builder
            ->add('gender', GenderType::class, [
                'label'          => false,
                'expanded'       => false,
                'required'       => $options['required'],
                'choices'        => $options['gender_choices'],
                'placeholder'    => 'ekyna_core.value.choose',
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'identity-gender',
                ],
            ])
            ->add('lastName', TextType::class, [
                'label'          => false,
                'required'       => $options['required'],
                'attr'           => [
                    'class'        => 'identity-last-name',
                    'placeholder'  => 'ekyna_core.field.last_name',
                    'maxlength'    => 35,
                    'autocomplete' => $section . 'given-name',
                ],
                'error_bubbling' => true,
            ])
            ->add('firstName', TextType::class, [
                'label'          => false,
                'required'       => $options['required'],
                'attr'           => [
                    'class'        => 'identity-first-name',
                    'placeholder'  => 'ekyna_core.field.first_name',
                    'maxlength'    => 35,
                    'autocomplete' => $section . 'family-name',
                ],
                'error_bubbling' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'     => 'Ekyna\Component\Commerce\Common\Model\IdentityInterface',
                'gender_choices' => call_user_func($this->gendersClass . '::getChoices'),
                'label'          => 'ekyna_core.field.identity',
                'inherit_data'   => true,
                'required'       => true,
                'error_bubbling' => false,
                'section'        => null,
            ])
            ->setAllowedTypes('gender_choices', ['callable', 'array'])
            ->setAllowedTypes('section', ['string', 'null']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_identity';
    }
}
