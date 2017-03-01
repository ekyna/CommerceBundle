<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CountryChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CountryChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $countryClass;


    /**
     * Constructor.
     *
     * @param string $countryClass
     */
    public function __construct($countryClass)
    {
        $this->countryClass = $countryClass;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'         => 'ekyna_commerce.country.label.singular',
            'class'         => $this->countryClass,
            'query_builder' => function (EntityRepository $er) {
                $qb = $er->createQueryBuilder('o');

                return $qb->andWhere($qb->expr()->eq('o.enabled', true));
            },
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ResourceType::class;
    }
}
