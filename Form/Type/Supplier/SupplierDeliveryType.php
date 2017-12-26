<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\DataTransformer\SupplierDeliveryItemsTransformer;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

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

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'commerce-supplier-delivery');
    }
}
