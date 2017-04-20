<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Notify;

use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RecipientsType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Notification
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RecipientsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type'   => RecipientType::class,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_recipients';
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}
