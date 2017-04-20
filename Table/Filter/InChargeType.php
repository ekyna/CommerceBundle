<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Filter;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Repository\GroupRepositoryInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Filter\EntityType;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class InChargeType
 * @package Ekyna\Bundle\CommerceBundle\Table\Filter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InChargeType extends AbstractFilterType
{
    private GroupRepositoryInterface $groupRepository;
    private string                   $userClass;

    public function __construct(GroupRepositoryInterface $groupRepository, string $userClass)
    {
        $this->groupRepository = $groupRepository;
        $this->userClass = $userClass;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label'         => t('customer.field.in_charge', [], 'EkynaCommerce'),
                'class'         => $this->userClass,
                'entity_label'  => 'shortName',
                'query_builder' => function (EntityRepository $repository) {
                    $qb = $repository->createQueryBuilder('u');

                    return $qb
                        ->andWhere($qb->expr()->in('u.group', ':groups'))
                        ->andWhere($qb->expr()->in('u.enabled', ':enabled'))
                        ->setParameter('groups', $this->groupRepository->findByRole('ROLE_ADMIN'))
                        ->setParameter('enabled', true)
                        ->orderBy('u.firstName', 'ASC')
                        ->orderBy('u.lastName', 'ASC');
                },
            ]);
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
