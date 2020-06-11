<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MentionType;
use Ekyna\Component\Commerce\Pricing\Entity\TaxRuleMentionTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TaxRuleMentionType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxRuleMentionType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('translation_class', TaxRuleMentionTranslation::class);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return MentionType::class;
    }
}
