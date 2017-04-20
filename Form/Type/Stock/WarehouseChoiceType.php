<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Stock;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Component\Commerce\Stock\Model\WarehouseInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class WarehouseChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Stock
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class WarehouseChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'             => function (Options $options, $value) {
                if (false === $value || !empty($value)) {
                    return $value;
                }

                return t('warehouse.label.' . ($options['multiple'] ? 'plural' : 'singular'), [], 'EkynaCommerce');
            },
            'resource'          => 'ekyna_commerce.warehouse',
            'preferred_choices' => function (WarehouseInterface $warehouse) {
                return $warehouse->isDefault();
            },
            'query_builder'     => function (EntityRepository $repository) {
                $qb = $repository->createQueryBuilder('w');

                return $qb
                    // Temporary restrict to default warehouse
                    // TODO Remove when stock unit <=> warehouse relation will be handled
                    ->andWhere($qb->expr()->eq('w.default', true))
                    ->orderBy('w.name', 'ASC');
            },
        ]);
    }

    public function getParent(): ?string
    {
        return ResourceChoiceType::class;
    }
}
