<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Checkout;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class InformationType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Checkout
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InformationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('company', Type\TextType::class, [
                'label'    => t('field.company', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'placeholder'  => t('address.field.company', [], 'EkynaCommerce'),
                    'maxlength'    => 35,
                    'autocomplete' => 'organization',
                ],
            ])
            ->add('identity', IdentityType::class, [
                'required' => true,
            ])
            ->add('email', Type\EmailType::class, [
                'label'    => t('field.email', [], 'EkynaUi'),
                'required' => true,
                'attr'     => [
                    'autocomplete' => 'email',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', CartInterface::class);
    }
}
