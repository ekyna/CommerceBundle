<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type as Commerce;
use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\LocaleChoiceType;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Symfony\Component\Form\Extension\Core\Type as Symfony;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class SupplierType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierType extends ResourceFormType
{
    /**
     * @var SupplierProductRepositoryInterface
     */
    private $supplierProductRepository;


    /**
     * Constructor.
     *
     * @param SupplierProductRepositoryInterface $repository
     * @param string                             $supplierClass
     */
    public function __construct(SupplierProductRepositoryInterface $repository, string $supplierClass)
    {
        parent::__construct($supplierClass);

        $this->supplierProductRepository = $repository;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Symfony\TextType::class, [
                'label' => 'ekyna_core.field.name',
            ])
            ->add('customerCode', Symfony\TextType::class, [
                'label'    => 'ekyna_commerce.supplier.field.customer_code',
                'required' => false,
            ])
            ->add('tax', Commerce\Pricing\TaxChoiceType::class, [
                'required' => false,
            ])
            ->add('carrier', SupplierCarrierChoiceType::class, [
                'allow_new' => true,
            ])
            ->add('identity', Commerce\Common\IdentityType::class, [
                'required' => false,
            ])
            ->add('email', Symfony\EmailType::class, [
                'label' => 'ekyna_core.field.email',
            ])
            ->add('address', SupplierAddressType::class, [
                'label'    => 'ekyna_core.field.address',
                'required' => false,
            ])
            ->add('description', TinymceType::class, [
                'label'    => 'ekyna_core.field.description',
                'theme'    => 'simple',
                'required' => false,
            ])
            ->add('locale', LocaleChoiceType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierInterface $data */
            $data = $event->getData();
            $form = $event->getForm();

            $products = [];
            if ((null !== $data) && (null !== $data->getId())) {
                $products = $this->supplierProductRepository->findBy(['supplier' => $data], [], 1);
            }

            $form->add('currency', Commerce\Common\CurrencyChoiceType::class, [
                'disabled' => !empty($products),
            ]);
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierInterface $data */
            $data = $event->getData();

            if (null === $address = $data->getAddress()) {
                return null;
            }

            if ($address->isEmpty()) {
                $data->setAddress(null);
            }
        }, 2048);
    }
}
