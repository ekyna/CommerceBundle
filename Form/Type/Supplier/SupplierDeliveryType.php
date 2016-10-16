<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\DataTransformer\SupplierDeliveryItemsTransformer;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SupplierDeliveryType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryType extends ResourceFormType
{
    /**
     * @var ResourceRepositoryInterface
     */
    private $deliveryItemRepository;


    /**
     * Constructor.
     *
     * @param string                      $dataClass
     * @param ResourceRepositoryInterface $deliveryItemRepository
     */
    public function __construct($dataClass, ResourceRepositoryInterface $deliveryItemRepository)
    {
        parent::__construct($dataClass);

        $this->deliveryItemRepository = $deliveryItemRepository;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('items', SupplierDeliveryItemsType::class)
            ->addModelTransformer(
                new SupplierDeliveryItemsTransformer($this->deliveryItemRepository)
            );
    }
}
