<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PaymentTermChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentTermChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $queryBuilder = function (Options $options) {
            return function (EntityRepository $repository) use ($options) {
                $qb = $repository->createQueryBuilder('t');
                $qb
                    ->addOrderBy('t.days', 'ASC')
                    ->addOrderBy('t.endOfMonth', 'DESC');

                return $qb;
            };
        };

        $resolver
            ->setDefaults([
                'resource'      => 'ekyna_commerce.payment_term',
                'required'      => false,
                'select2'       => false,
                'query_builder' => $queryBuilder,
            ]);
    }

    public function getParent(): ?string
    {
        return ResourceChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_payment_term_choice';
    }
}
