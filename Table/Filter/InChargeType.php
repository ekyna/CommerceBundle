<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Filter;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Repository\GroupRepositoryInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Filter\EntityType;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InChargeType
 * @package Ekyna\Bundle\CommerceBundle\Table\Filter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InChargeType extends AbstractFilterType
{
    /**
     * @var string
     */
    private $userClass;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;


    /**
     * Constructor.
     *
     * @param GroupRepositoryInterface $groupRepository
     * @param string                   $userClass
     */
    public function __construct(GroupRepositoryInterface $groupRepository, $userClass)
    {
        $this->groupRepository = $groupRepository;
        $this->userClass = $userClass;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'         => 'ekyna_commerce.customer.field.in_charge',
                'class'         => $this->userClass,
                'entity_label'  => 'shortName',
                'query_builder' => function (EntityRepository $repository) {
                    $qb = $repository->createQueryBuilder('u');

                    return $qb
                        ->andWhere($qb->expr()->in('u.group', ':groups'))
                        ->andWhere($qb->expr()->in('u.active', ':active'))
                        ->setParameter('groups', $this->groupRepository->findByRole('ROLE_ADMIN'))
                        ->setParameter('active', true)
                        ->orderBy('u.firstName', 'ASC')
                        ->orderBy('u.lastName', 'ASC');
                },
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return EntityType::class;
    }
}
