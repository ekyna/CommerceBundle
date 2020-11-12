<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Stock;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

/**
 * Class ResupplyAlertSubscribeType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Stock
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResupplyAlertSubscribeType extends AbstractType
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
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_resupply_alert_subscribe';
    }
}
