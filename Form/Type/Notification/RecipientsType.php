<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Notification;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RecipientsType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Notification
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RecipientsType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type'   => RecipientType::class,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_recipients';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
