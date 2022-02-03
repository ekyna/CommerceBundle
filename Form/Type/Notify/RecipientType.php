<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Notify;

use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Model\Recipient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class RecipientType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Notification
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RecipientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => false,
                'attr'  => [
                    'placeholder' => t('field.email', [], 'EkynaUi'),
                ],
                'empty_data' => '',
            ])
            ->add('name', TextType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => [
                    'placeholder' => t('field.name', [], 'EkynaUi'),
                ],
                'empty_data' => '',
            ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'commerce-recipient');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipient::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_recipient';
    }
}
