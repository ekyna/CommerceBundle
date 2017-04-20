<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AddressType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class CustomerAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddressType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['defaults']) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var CustomerAddressInterface $address */
                $address = $event->getData();
                $form = $event->getForm();

                $form
                    ->add('invoiceDefault', CheckboxType::class, [
                        'label'    => t('customer_address.field.invoice_default', [], 'EkynaCommerce'),
                        'disabled' => $address->isInvoiceDefault(),
                        'required' => false,
                        'attr'     => [
                            'align_with_widget' => true,
                        ],
                    ])
                    ->add('deliveryDefault', CheckboxType::class, [
                        'label'    => t('customer_address.field.delivery_default', [], 'EkynaCommerce'),
                        'disabled' => $address->isDeliveryDefault(),
                        'required' => false,
                        'attr'     => [
                            'align_with_widget' => true,
                        ],
                    ]);
            });
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('defaults', true)
            ->setAllowedTypes('defaults', 'bool');
    }

    public function getParent(): ?string
    {
        return AddressType::class;
    }
}
