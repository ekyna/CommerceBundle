<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\TinymceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class CouponType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProjectType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label'    => t('field.name', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('description', TinymceType::class, [
                'label'    => t('field.description', [], 'EkynaUi'),
                'required' => false,
                'theme'    => 'light',
            ]);
    }
}
