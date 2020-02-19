<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\CoreBundle\Form\Type\UploadType;
use Ekyna\Component\Commerce\Customer\Entity\CustomerLogo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomerLogoType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerLogoType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'      => 'ekyna_core.field.logo',
                'data_class' => CustomerLogo::class,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return UploadType::class;
    }
}
