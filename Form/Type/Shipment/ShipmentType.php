<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Model\ShipmentStates;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ShipmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentType extends ResourceFormType
{
    /**
     * @var string
     */
    private $methodClass;


    /**
     * Constructor.
     *
     * @param string $dataClass
     * @param string $methodClass
     */
    public function __construct($dataClass, $methodClass)
    {
        parent::__construct($dataClass);

        $this->methodClass = $methodClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', Type\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'disabled' => true,
            ])
            ->add('state', Type\ChoiceType::class, [
                'label'    => 'ekyna_core.field.status',
                'choices'  => ShipmentStates::getChoices(),
                'disabled' => true,
            ])
            ->add('method', EntityType::class, [
                'label'         => 'ekyna_commerce.shipment_method.label.singular',
                'class'         => $this->methodClass,
                'query_builder' => function (EntityRepository $repository) {
                    $qb = $repository
                        ->createQueryBuilder('m')
                        ->andWhere('m.enabled = :enabled')
                        ->setParameter('enabled', true);

                    return $qb;
                },
            ])
            ->add('description', Type\TextareaType::class, [
                'label' => 'ekyna_core.field.description',
            ]);
    }
}
