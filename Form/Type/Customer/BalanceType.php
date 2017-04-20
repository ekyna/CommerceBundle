<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Component\Commerce\Customer\Balance\Balance;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;
use function ucfirst;

/**
 * Class BalanceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BalanceType extends AbstractType
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('from', Type\DateType::class, [
                'label'          => t('field.from_date', [], 'EkynaUi'),
                'required'       => false,
                /*'picker_options' => [
                    'widgetPositioning' => [
                        'horizontal' => 'left',
                    ],
                ],*/
            ])
            ->add('to', Type\DateType::class, [
                'label'    => t('field.to_date', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('filter', Type\ChoiceType::class, [
                'label'    => t('button.filter', [], 'EkynaUi'),
                'required' => true,
                'select2'  => false,
                'choices'  => [
                    'customer.balance.all'             => Balance::FILTER_ALL,
                    'customer.balance.due_invoices'    => Balance::FILTER_DUE_INVOICES,
                    'customer.balance.befall_invoices' => Balance::FILTER_BEFALL_INVOICES,
                ],
                'choice_translation_domain' => 'EkynaCommerce'
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Balance $balance */
            $balance = $event->getData();
            $customer = $balance->getCustomer();

            $currencies = $this->orderRepository->getCustomerCurrencies($customer);

            $choices = [];
            foreach ($currencies as $currency) {
                $choices[ucfirst(Currencies::getName($currency))] = $currency;
            }

            $event->getForm()->add('currency', Type\ChoiceType::class, [
                'label'    => t('field.currency', [], 'EkynaUi'),
                'required' => true,
                'select2'  => false,
                'choices'  => $choices,
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Balance::class);
    }
}
