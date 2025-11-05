<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Manufacture;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Model\BOMState;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BillOfMaterialsChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Manufacture
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BillOfMaterialsChoiceType extends AbstractType
{
    public function __construct(
        private readonly SubjectHelperInterface $subjectHelper,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'resource'          => BillOfMaterialsInterface::class,
            'choice_label' => ChoiceList::label($this, [$this, 'createChoiceLabel']),
            'query_builder'     => function (EntityRepository $repository) {
                $qb = $repository->createQueryBuilder('b');

                return $qb
                    ->andWhere($qb->expr()->eq('b.state', $qb->expr()->literal(BOMState::VALIDATED->value)))
                    ->addOrderBy('b.number', 'DESC')
                    ->addOrderBy('b.version', 'DESC');
            },
        ]);
    }

    public function createChoiceLabel(BillOfMaterialsInterface $bom): string
    {
        $subject = $this->subjectHelper->resolve($bom);

        return sprintf(
            '%s-v%d [%s] %s',
            $bom->getNumber(),
            $bom->getVersion(),
            $subject->getReference(),
            $subject->getDesignation()
        );
    }

    public function getParent(): ?string
    {
        return ResourceChoiceType::class;
    }
}
