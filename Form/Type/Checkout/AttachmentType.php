<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Checkout;

use Ekyna\Bundle\CommerceBundle\Form\Type\Cart\CartAttachmentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Checkout
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttachmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('internal');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('rename_field', false);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_checkout_attachment';
    }

    public function getParent(): ?string
    {
        return CartAttachmentType::class;
    }
}
