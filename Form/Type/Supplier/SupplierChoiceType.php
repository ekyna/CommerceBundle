<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SupplierChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'         => t('supplier.label.singular', [], 'EkynaCommerce'),
            'resource'      => 'ekyna_commerce.supplier',
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('s')->orderBy('s.name', 'ASC');
            },
        ]);
    }

    public function getParent(): ?string
    {
        return ResourceChoiceType::class;
    }
}
