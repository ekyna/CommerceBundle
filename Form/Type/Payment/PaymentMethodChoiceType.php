<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PaymentMethodChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodChoiceType extends AbstractType
{
    /**
     * @var string
     */
    protected $dataClass;


    /**
     * Constructor.
     *
     * @param string $dataClass
     */
    public function __construct($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $queryBuilder = function (Options $options) {
            return function (EntityRepository $repository) use ($options) {
                $qb = $repository->createQueryBuilder('m');
                $qb->andWhere($qb->expr()->eq('m.enabled', true));
                if ($options['available']) {
                    $qb->andWhere($qb->expr()->eq('m.available', true));
                }

                return $qb;
            };
        };

        $resolver
            ->setDefaults([
                'label'         => false,
                'expanded'      => true,
                'available'     => true,
                'class'         => $this->dataClass,
                'query_builder' => $queryBuilder,
            ])
            ->setAllowedTypes('available', 'bool');
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_payment_method_choice';
    }
}
