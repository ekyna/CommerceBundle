<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerAddressRepositoryInterface;
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
     * @var CustomerAddressRepositoryInterface
     */
    private $customerAddressRepository;


    /**
     * Constructor.
     *
     * @param SerializerInterface                $serializer
     * @param CustomerAddressRepositoryInterface $customerAddressRepository
     */
    public function __construct(
        SerializerInterface $serializer,
        CustomerAddressRepositoryInterface $customerAddressRepository
    ) {
        $this->serializer = $serializer;
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
            'attr'          => [
                'widget_col' => 12,
            ],
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $sale = $event->getData();
            $form = $event->getForm();

            // Check if data (sale) is set.
            if (!$sale instanceof SaleInterface) {
                return;
            }
            // Check if customer is set.
            if (null === $customer = $sale->getCustomer()) {
                return;
            }

            $addresses = $this->customerAddressRepository->findByCustomer($customer);
            if (empty($addresses)) {
                return;
            }

            $choices = [];
            foreach ($addresses as $address) {
                $choices[(string)$address] = $address->getId();
            }

            $form->add('choice', Type\ChoiceType::class, [
                'label'       => 'ekyna_commerce.sale.field.address_choice',
                'choices'     => $choices,
                'choice_attr' => function ($val, $key, $index) use ($addresses) {
                    if (!isset($addresses[$val])) {
                        return [];
                    }

                    $data = $this
                        ->serializer
                        ->serialize($addresses[$val], 'json', ['groups' => ['Default']]);

                    return ['data-address' => $data];
                },
                'required'    => false,
                'mapped'      => false,
                'attr'        => [
                    'class' => 'sale-address-choice',
                ],
            ]);
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
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'delivery'     => false,
                'data_class'   => SaleInterface::class,
                'address_type' => null,
            ])
            ->setAllowedTypes('address_type', 'string'); // TODO validate
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_sale_address';
    }
}
