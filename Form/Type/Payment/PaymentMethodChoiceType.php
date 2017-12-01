<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use Doctrine\ORM\EntityRepository;
use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants as Offline;
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

                if ($options['disabled']) {
                    $qb
                        ->andWhere('m.factoryName = :factoryName')
                        ->setParameter('factoryName', Offline::FACTORY_NAME);
                } else {
                    if ($options['enabled']) {
                        $qb->andWhere($qb->expr()->eq('m.enabled', true));
                    }
                    if ($options['available']) {
                        $qb->andWhere($qb->expr()->eq('m.available', true));
                    }
                }

                return $qb;
            };
        };

        $resolver
            ->setDefaults([
                'label'         => 'ekyna_commerce.payment_method.label.singular',
                'enabled'       => false,
                'available'     => false,
                'class'         => $this->dataClass,
                'query_builder' => $queryBuilder,
            ])
            ->setAllowedTypes('enabled', 'bool')
            ->setAllowedTypes('available', 'bool')
            ->setNormalizer('enabled', function(Options $options, $value) {
                if ($options['disabled'] || $options['available']) {
                    return true;
                }

                return $value;
            });
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
