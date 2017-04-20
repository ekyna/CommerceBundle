<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class ShipmentMethodChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'         => t('shipment_method.label.singular', [], 'EkynaCommerce'),
            'resource'      => 'ekyna_commerce.shipment_method',
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('m')->orderBy('m.name', 'ASC');
            },
        ]);
    }

    public function getParent(): ?string
    {
        return ResourceChoiceType::class;
    }
}
