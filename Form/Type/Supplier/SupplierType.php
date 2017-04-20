<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\CommerceBundle\Form\Type as Commerce;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\LocaleChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Bundle\UiBundle\Form\Type\TinymceType;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Symfony\Component\Form\Extension\Core\Type as Symfony;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class SupplierType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierType extends AbstractResourceType
{
    private SupplierProductRepositoryInterface $supplierProductRepository;


    public function __construct(SupplierProductRepositoryInterface $repository)
    {
        $this->supplierProductRepository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Symfony\TextType::class, [
                'label' => t('field.name', [], 'EkynaUi'),
            ])
            ->add('customerCode', Symfony\TextType::class, [
                'label'    => t('supplier.field.customer_code', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('tax', ResourceChoiceType::class, [
                'resource' => 'ekyna_commerce.tax',
                'required' => false,
            ])
            ->add('carrier', ResourceChoiceType::class, [
                'resource'  => 'ekyna_commerce.supplier_carrier',
                'allow_new' => true,
            ])
            ->add('identity', Commerce\Common\IdentityType::class, [
                'required' => false,
            ])
            ->add('email', Symfony\EmailType::class, [
                'label' => t('field.email', [], 'EkynaUi'),
            ])
            ->add('address', SupplierAddressType::class, [
                'label'    => t('field.address', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('description', TinymceType::class, [
                'label'    => t('field.description', [], 'EkynaUi'),
                'theme'    => 'simple',
                'required' => false,
            ])
            ->add('locale', LocaleChoiceType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var SupplierInterface $data */
            $data = $event->getData();
            $form = $event->getForm();

            $hasProduct = false;
            if ((null !== $data) && (null !== $data->getId())) {
                $hasProduct = $this->supplierProductRepository->existsForSupplier($data);
            }

            $form->add('currency', Commerce\Common\CurrencyChoiceType::class, [
                'disabled' => $hasProduct,
            ]);
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            /** @var SupplierInterface $data */
            $data = $event->getData();

            if (null === $address = $data->getAddress()) {
                return;
            }

            if ($address->isEmpty()) {
                $data->setAddress(null);
            }
        }, 2048);
    }
}
