<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\CommerceBundle\Form\DataTransformer\SupplierDeliveryItemsTransformer;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class SupplierDeliveryType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryType extends AbstractResourceType
{
    private ResourceFactoryInterface $deliveryItemFactory;
    private SubjectHelperInterface   $subjectHelper;


    public function __construct(
        ResourceFactoryInterface $deliveryItemFactory,
        SubjectHelperInterface $subjectHelper
    ) {
        $this->deliveryItemFactory = $deliveryItemFactory;
        $this->subjectHelper = $subjectHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('items', SupplierDeliveryItemsType::class)
            ->addModelTransformer(
                new SupplierDeliveryItemsTransformer($this->deliveryItemFactory, $this->subjectHelper)
            );
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'commerce-supplier-delivery');
    }
}
