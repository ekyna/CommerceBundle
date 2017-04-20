<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Stock;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

use function Symfony\Component\Translation\t;

/**
 * Class ResupplyAlertSubscribeType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Stock
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResupplyAlertSubscribeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label'       => t('field.email', [], 'EkynaUi'),
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Email(),
                ],
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_resupply_alert_subscribe';
    }
}
