<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates as States;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form;

/**
 * Class PaymentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentType extends ResourceFormType
{
    /**
     * @var string
     */
    private $currencyClass;

    /**
     * @var string
     */
    private $methodClass;


    /**
     * Constructor.
     *
     * @param string $dataClass
     * @param string $currencyClass
     * @param string $methodClass
     */
    public function __construct($dataClass, $currencyClass, $methodClass)
    {
        parent::__construct($dataClass);

        $this->currencyClass = $currencyClass;
        $this->methodClass = $methodClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(Form\FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(Form\FormEvents::PRE_SET_DATA, function (Form\FormEvent $event) use ($options) {
            $form = $event->getForm();

            /** @var \Ekyna\Component\Commerce\Payment\Entity\AbstractPayment $payment */
            $payment = $event->getData();

            $adminMode = $options['admin_mode'];
            /** @var \Ekyna\Component\Commerce\Common\Model\CurrencyInterface $currency */
            $currency = null !== $payment ? $payment->getCurrency() : null;
            /** @var \Ekyna\Bundle\CommerceBundle\Entity\PaymentMethod $method */
            $method = null !== $payment ? $payment->getMethod() : null;

            $lockedMethod = (null !== $method) && ($method->getFactoryName() !== 'offline');
            $lockedAmount = $lockedMethod || (
                null !== $payment &&
                !(null !== $method && $method->isManual()) &&
                $payment->getState() !== States::STATE_NEW
            );

            $form->add('amount', MoneyType::class, [
                'label'    => 'ekyna_core.field.amount',
                'currency' => $currency ? $currency->getCode() : 'EUR', // TODO default user currency
                'disabled' => $lockedAmount || !$adminMode,
            ]);

            if ($adminMode) {
                $lockedCurrency = null !== $currency;

                $form
                    ->add('number', Type\TextType::class, [
                        'label'    => 'ekyna_core.field.number',
                        'disabled' => true,
                    ])
                    // TODO test amount field behavior if currency changes
                    ->add('currency', EntityType::class, [
                        'label'         => 'ekyna_commerce.currency.label.singular',
                        'class'         => $this->currencyClass,
                        'disabled'      => $lockedCurrency,
                        'query_builder' => function (EntityRepository $repository) {
                            $qb = $repository
                                ->createQueryBuilder('m')
                                ->andWhere('m.enabled = :enabled')
                                ->setParameter('enabled', true);

                            return $qb;
                        },
                    ])
                    ->add('state', Type\ChoiceType::class, [
                        'label'    => 'ekyna_core.field.status',
                        'choices'  => PaymentStates::getChoices(),
                        'disabled' => $lockedMethod,
                    ])
                    ->add('method', EntityType::class, [
                        'label'         => 'ekyna_commerce.payment_method.label.singular',
                        'class'         => $this->methodClass,
                        'disabled'      => $lockedMethod,
                        'query_builder' => function (EntityRepository $repository) use ($lockedMethod) {
                            $qb = $repository
                                ->createQueryBuilder('m')
                                ->andWhere('m.enabled = :enabled')
                                ->setParameter('enabled', true);

                            if (!$lockedMethod) {
                                $qb
                                    ->andWhere('m.factoryName = :factoryName')
                                    ->setParameter('factoryName', 'offline');
                            }

                            return $qb;
                        },
                    ])
                    ->add('description', Type\TextareaType::class, [
                        'label'    => 'ekyna_core.field.description',
                        'required' => false,
                    ]);
            } else {
                $form->add('method', PaymentMethodChoiceType::class, [
                    'label' => 'ekyna_commerce.payment_method.label.singular',
                ]);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    /* TODO public function getBlockPrefix()
    {
        return 'ekyna_commerce_payment';
    }*/
}
