<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\UiBundle\Form\Type\UploadType;
use Ekyna\Component\Commerce\Customer\Entity\CustomerLogo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class CustomerLogoType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerLogoType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label'      => t('field.logo', [], 'EkynaUi'),
                'data_class' => CustomerLogo::class,
            ]);
    }

    public function getParent(): ?string
    {
        return UploadType::class;
    }
}
