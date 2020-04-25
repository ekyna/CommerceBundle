<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Stock;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Component\Commerce\Stock\Model\WarehouseInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WarehouseChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Stock
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class WarehouseChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $warehouseClass;


    /**
     * Constructor.
     *
     * @param string $warehouseClass
     */
    public function __construct(string $warehouseClass)
    {
        $this->warehouseClass = $warehouseClass;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'             => function (Options $options, $value) {
                if (false === $value || !empty($value)) {
                    return $value;
                }

                return 'ekyna_commerce.warehouse.label.' . ($options['multiple'] ? 'plural' : 'singular');
            },
            'class'             => $this->warehouseClass,
            'preferred_choices' => function (WarehouseInterface $warehouse) {
                return $warehouse->isDefault();
            },
            'query_builder' => function (EntityRepository $repository) {
                $qb = $repository->createQueryBuilder('w');

                return $qb
                    // Temporary restrict to default warehouse
                    // TODO Remove when stock unit <=> warehouse relation will be handled
                    ->andWhere($qb->expr()->eq('w.default', true))
                    ->orderBy('w.name', 'ASC');
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
