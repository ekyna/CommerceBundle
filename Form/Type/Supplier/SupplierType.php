<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type as Commerce;
use Symfony\Component\Form\Extension\Core\Type as Symfony;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class SupplierType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Symfony\TextType::class, [
                'label' => 'ekyna_core.field.name',
            ])
            ->add('currency', Commerce\Common\CurrencyChoiceType::class)
            ->add('identity', Commerce\Common\IdentityType::class)
            ->add('email', Symfony\EmailType::class, [
                'label'    => 'ekyna_core.field.email',
            ])
            ->add('address', SupplierAddressType::class, [
                'label' => 'ekyna_core.field.address',
                'required' => false,
            ]);
    }
}
