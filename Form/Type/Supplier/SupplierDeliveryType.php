<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\DataTransformer\SupplierDeliveryItemsTransformer;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Symfony\Component\Form\FormBuilderInterface;

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
     * @var SubjectHelperInterface
     */
    private $subjectHelper;


    /**
     * Constructor.
     *
     * @param ResourceRepositoryInterface $deliveryItemRepository
     * @param SubjectHelperInterface      $subjectHelper
     * @param string                      $dataClass
     */
    public function __construct(
        ResourceRepositoryInterface $deliveryItemRepository,
        SubjectHelperInterface $subjectHelper,
        $dataClass
    ) {
        parent::__construct($dataClass);

        $this->deliveryItemRepository = $deliveryItemRepository;
        $this->subjectHelper = $subjectHelper;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('items', SupplierDeliveryItemsType::class)
            ->addModelTransformer(
                new SupplierDeliveryItemsTransformer($this->deliveryItemRepository, $this->subjectHelper)
            );
    }
}
