<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MentionType;
use Ekyna\Component\Commerce\Payment\Entity\PaymentMethodMentionTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PaymentMethodMentionType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodMentionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('translation_class', PaymentMethodMentionTranslation::class);
    }

    public function getParent(): ?string
    {
        return MentionType::class;
    }
}
