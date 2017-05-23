<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TaxGroupChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxGroupChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $taxGroupClass;


    /**
     * Constructor.
     *
     * @param string $taxGroupClass
     */
    public function __construct($taxGroupClass)
    {
        $this->taxGroupClass = $taxGroupClass;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'ekyna_commerce.tax_group.label.singular',
            'class' => $this->taxGroupClass,
            'attr'  => [
                'placeholder' => 'ekyna_commerce.tax_group.label.singular',
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ResourceType::class;
    }
}
