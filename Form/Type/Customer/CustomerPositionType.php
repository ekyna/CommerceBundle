<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class CustomerPositionType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerPositionType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label' => t('field.name', [], 'EkynaUi'),
        ]);
    }
}
