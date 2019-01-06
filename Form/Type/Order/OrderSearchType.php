<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Order;

use Ekyna\Bundle\CoreBundle\Form\Type\EntitySearchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrderSearchType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderSearchType extends AbstractType
{
    /**
     * @var string
     */
    private $orderClass;


    /**
     * Constructor.
     *
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->orderClass = $class;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'    => function (Options $options, $value) {
                    if (null !== $value) {
                        return $value;
                    }

                    return 'ekyna_commerce.order.label.' . ($options['multiple'] ? 'plural' : 'singular');
                },
                'class'    => $this->orderClass,
                'route'    => 'ekyna_commerce_order_admin_search',
                'required' => false,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return EntitySearchType::class;
    }
}
