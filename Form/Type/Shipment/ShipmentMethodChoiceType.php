<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentMethodChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $methodClass;


    /**
     * Constructor.
     *
     * @param string $methodClass
     */
    public function __construct($methodClass)
    {
        $this->methodClass = $methodClass;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'         => 'ekyna_commerce.supplier.label.singular',
            'class'         => $this->methodClass,
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('m')->orderBy('m.name', 'ASC');
            },
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
