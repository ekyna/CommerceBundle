<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Stock;

use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

/**
 * Class ResupplyAlertUnsubscribeType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Stock
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResupplyAlertUnsubscribeType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label'       => 'ekyna_core.field.email',
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Email(),
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('message', 'ekyna_commerce.resupply_alert.unsubscribe.confirm');
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_resupply_alert_unsubscribe';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ConfirmType::class;
    }
}
