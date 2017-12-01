<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Model\PaymentStates;
use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants as Offline;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
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
        if (!$options['admin_mode']) {
            throw new LogicException("This form should not be used a public pages.");
        }

        $builder->addEventListener(Form\FormEvents::PRE_SET_DATA, function (Form\FormEvent $event) use ($options) {
            $form = $event->getForm();

            $payment = $event->getData();
            if (!$payment instanceof PaymentInterface) {
                throw new LogicException("Expected instance of " . PaymentInterface::class);
            }
            if (null === $currency = $payment->getCurrency()) {
                throw new LogicException("Payment currency must be set.");
            }
            if (null === $method = $payment->getMethod()) {
                throw new LogicException("Payment method must be set.");
            }

            $methodDisabled = !$method->isManual();
            $amountDisabled = !($method->isManual() || $method->isOutstanding() || $method->isCredit());

            $form
                ->add('amount', MoneyType::class, [
                    'label'    => 'ekyna_core.field.amount',
                    'currency' => $currency->getCode(),
                    'disabled' => $amountDisabled,
                ])
                ->add('number', Type\TextType::class, [
                    'label'    => 'ekyna_core.field.number',
                    'disabled' => true,
                ])
                // TODO Use CurrencyChoiceType
                ->add('currency', EntityType::class, [
                    'label'         => 'ekyna_commerce.currency.label.singular',
                    'class'         => $this->currencyClass,
                    'disabled'      => true,
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
                    'disabled' => true,
                ])
                // TODO Use PaymentMethodChoiceType
                ->add('method', EntityType::class, [
                    'label'         => 'ekyna_commerce.payment_method.label.singular',
                    'class'         => $this->methodClass,
                    'disabled'      => $methodDisabled,
                    'query_builder' => function (EntityRepository $repository) use ($methodDisabled) {
                        $qb = $repository
                            ->createQueryBuilder('m')
                            ->andWhere('m.enabled = :enabled')
                            ->setParameter('enabled', true);

                        if (!$methodDisabled) {
                            $qb
                                ->andWhere('m.factoryName = :factoryName')
                                ->setParameter('factoryName', Offline::FACTORY_NAME);
                        }

                        return $qb;
                    },
                ])
                ->add('description', Type\TextareaType::class, [
                    'label'    => 'ekyna_commerce.field.description',
                    'required' => false,
                ]);
        });
    }
}
