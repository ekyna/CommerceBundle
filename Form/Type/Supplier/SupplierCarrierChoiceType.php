<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SupplierCarrierChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierCarrierChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $carrierClass;


    /**
     * Constructor.
     *
     * @param string $carrierClass
     */
    public function __construct(string $carrierClass)
    {
        $this->carrierClass = $carrierClass;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => function (Options $options, $value) {
                if (false === $value || !empty($value)) {
                    return $value;
                }

                return 'ekyna_commerce.supplier_carrier.label.' . ($options['multiple'] ? 'plural' : 'singular');
            },
            'class' => $this->carrierClass,
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
