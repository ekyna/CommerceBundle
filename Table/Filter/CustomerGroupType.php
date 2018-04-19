<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Filter;

use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Filter\EntityType;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomerGroupType
 * @package Ekyna\Bundle\CommerceBundle\Table\Filter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupType extends AbstractFilterType
{
    /**
     * @var string
     */
    private $class;


    /**
     * Constructor.
     *
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'        => 'ekyna_core.field.group',
            'class'        => $this->class,
            'entity_label' => 'name',
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