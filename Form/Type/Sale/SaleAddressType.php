<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerAddressRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class SaleAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleAddressType extends AbstractType
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ResourceRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerAddressRepositoryInterface
     */
    private $customerAddressRepository;


    /**
     * Constructor.
     *
     * @param SerializerInterface                $serializer
     * @param ResourceRepositoryInterface        $customerRepository
     * @param CustomerAddressRepositoryInterface $customerAddressRepository
     */
    public function __construct(
        SerializerInterface $serializer,
        ResourceRepositoryInterface $customerRepository,
        CustomerAddressRepositoryInterface $customerAddressRepository
    ) {
        $this->serializer = $serializer;
        $this->customerRepository = $customerRepository;
        $this->customerAddressRepository = $customerAddressRepository;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $propertyPath = 'invoiceAddress';
        $required = true;

        if ($options['delivery']) {
            $required = false;
            $propertyPath = 'deliveryAddress';

            $builder->add('sameAddress', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.sale.field.same_address',
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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
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

            $this->buildChoiceField($form, $sale->getCustomer(), $options);
        }, 2048);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
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
            $customer = $this->customerRepository->find($data['customer']);

            $this->buildChoiceField($form, $customer, $options);
        }, 2048);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
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

    /**
     * Builds the choice field.
     *
     * @param FormInterface     $form
     * @param CustomerInterface $customer
     * @param array             $options
     */
    private function buildChoiceField(FormInterface $form, CustomerInterface $customer = null, array $options)
    {
        if (!$options['customer_field'] && !$customer) {
            return;
        }

        $choiceOptions = [
            'label'    => 'ekyna_commerce.sale.field.address_choice',
            'choices'  => [],
            'disabled' => true,
            'required' => false,
            'mapped'   => false,
            'attr'     => [
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

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (0 < strlen($options['customer_field'])) {
            $view->vars['attr']['data-customer-field'] = $view->parent->vars['id'] . '_' . $options['customer_field'];
            $view->vars['attr']['data-mode'] = $options['delivery'] ? 'delivery' : 'invoice';
        }
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'delivery'       => false,
                'data_class'     => SaleInterface::class,
                'address_type'   => null,
                'customer_field' => null,
            ])
            ->setAllowedTypes('address_type', 'string')// TODO validate
            ->setAllowedTypes('customer_field', ['null', 'string']);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_sale_address';
    }
}
