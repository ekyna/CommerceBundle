<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerAddressRepositoryInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

use function Symfony\Component\Translation\t;

/**
 * Class SaleAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleAddressType extends AbstractType
{
    private SerializerInterface                $serializer;
    private ResourceRepositoryInterface        $customerRepository;
    private CustomerAddressRepositoryInterface $customerAddressRepository;


    public function __construct(
        SerializerInterface $serializer,
        ResourceRepositoryInterface $customerRepository,
        CustomerAddressRepositoryInterface $customerAddressRepository
    ) {
        $this->serializer = $serializer;
        $this->customerRepository = $customerRepository;
        $this->customerAddressRepository = $customerAddressRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $propertyPath = 'invoiceAddress';
        $required = true;

        if ($options['delivery']) {
            $required = false;
            $propertyPath = 'deliveryAddress';

            $builder->add('sameAddress', Type\CheckboxType::class, [
                'label'    => t('sale.field.same_address', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'class'             => 'sale-address-same',
                    'align_with_widget' => true,
                ],
            ]);
        }

        $builder->add('address', $options['address_type'], [
            'label'         => false,
            'property_path' => $propertyPath,
            'required'      => $required,
            'section'       => $options['delivery'] ? 'shipping' : 'billing',
            'attr'          => [
                'widget_col' => 12,
            ],
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
            $sale = $event->getData();
            $form = $event->getForm();

            // Remove the choice filed
            if ($form->has('choice')) {
                $form->remove('choice');
            }

            // Check if data (sale) is set.
            if (!$sale instanceof SaleInterface) {
                return;
            }

            $customer = $sale->getCustomer();
            if (!$options['customer_field'] && !$customer) {
                return;
            }

            if ($options['admin_mode'] || ($customer && $customer->getCustomerGroup()->isBusiness())) {
                $form->get('address')->add('information', Type\TextareaType::class, [
                    'label'    => t('field.information', [], 'EkynaUi'),
                    'required' => false,
                ]);
            }

            $this->buildChoiceField($form, $customer);
        }, 2048);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options): void {
            $data = $event->getData();
            $form = $event->getForm();

            // Abort if the data does not come from the parent form.
            if (!isset($data['customer'])) {
                return;
            }

            // Remove the choice filed
            if ($form->has('choice')) {
                $form->remove('choice');
            }

            /** @var CustomerInterface $customer */
            $customer = $this->customerRepository->find((int)$data['customer']);

            if (!$options['customer_field'] && !$customer) {
                return;
            }

            $this->buildChoiceField($form, $customer);
        }, 2048);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $sale = $event->getData();

            // Check if data (sale) is set.
            if (!$sale instanceof SaleInterface) {
                return;
            }

            if ($sale->isSameAddress() && null !== $sale->getDeliveryAddress()) {
                $sale->setDeliveryAddress(null);

                $event->setData($sale);
            }
        }, 2048);
    }

    private function buildChoiceField(FormInterface $form, CustomerInterface $customer = null): void
    {
        $choiceOptions = [
            'label'                     => t('sale.field.address_choice', [], 'EkynaCommerce'),
            'choices'                   => [],
            'choice_translation_domain' => false,
            'disabled'                  => true,
            'required'                  => false,
            'mapped'                    => false,
            'attr'                      => [
                'class' => 'sale-address-choice',
            ],
        ];

        // Check if customer is set.
        if ($customer) {
            $addresses = $this->customerAddressRepository->findByCustomerAndParents($customer);
            if (!empty($addresses)) {
                $choices = [];
                foreach ($addresses as $address) {
                    $choices[(string)$address] = $address->getId();
                }

                $choiceOptions['disabled'] = false;
                $choiceOptions['choices'] = $choices;
                $choiceOptions['choice_attr'] = function ($val) use ($addresses) {
                    if (!isset($addresses[$val])) {
                        return [];
                    }

                    $data = $this
                        ->serializer
                        ->serialize($addresses[$val], 'json', ['groups' => ['Default']]);

                    return ['data-address' => $data];
                };
            }
        }

        $form->add('choice', Type\ChoiceType::class, $choiceOptions);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (!empty($options['customer_field'])) {
            $view->vars['attr']['data-customer-field'] = $view->parent->vars['id'] . '_' . $options['customer_field'];
            $view->vars['attr']['data-mode'] = $options['delivery'] ? 'delivery' : 'invoice';
        }

        /** @var SaleInterface $sale */
        $sale = $form->getData();

        $view->vars['wrapped'] = $options['delivery'] && $sale->isSameAddress();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'delivery'       => false,
                'data_class'     => SaleInterface::class,
                'address_type'   => null,
                'customer_field' => null,
            ])
            ->setAllowedTypes('address_type', 'string')
            ->setAllowedTypes('customer_field', ['null', 'string']);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_sale_address';
    }
}
