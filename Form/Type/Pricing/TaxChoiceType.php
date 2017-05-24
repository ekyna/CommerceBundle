<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TaxChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $taxClass;


    /**
     * Constructor.
     *
     * @param string $taxClass
     */
    public function __construct($taxClass)
    {
        $this->taxClass = $taxClass;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label' => function(Options $options, $value) {
                    if (false === $value || !empty($value)) {
                        return $value;
                    }

                    return 'ekyna_commerce.tax.label.' . ($options['multiple'] ? 'plural' : 'singular');
                },
                'class' => $this->taxClass,
            ])
            ->setNormalizer('attr', function(Options $options, $value) {
                $value = (array) $value;

                if (!isset($value['placeholder'])) {
                    $value['placeholder'] = 'ekyna_commerce.tax.label.' . ($options['multiple'] ? 'plural' : 'singular');
                }

                return $value;
            });
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ResourceType::class;
    }
}
