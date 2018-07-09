<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants as Offline;
use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Constants as Credit;
use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants as Outstanding;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PaymentMethodChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodChoiceType extends AbstractType
{
    /**
     * @var string
     */
    protected $dataClass;


    /**
     * Constructor.
     *
     * @param string $dataClass
     */
    public function __construct($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $queryBuilder = function (Options $options) {
            return function (EntityRepository $repository) use ($options) {
                $qb = $repository
                    ->createQueryBuilder('m')
                    ->orderBy('m.position', 'ASC');

                $e = $qb->expr();

                if ($options['disabled']) {
                    $qb->andWhere($e->eq('m.factoryName', $e->literal(Offline::FACTORY_NAME)));
                } else {
                    if ($options['enabled']) {
                        $qb->andWhere($e->eq('m.enabled', true));
                    }
                    if ($options['available']) {
                        $qb->andWhere($e->eq('m.available', true));
                    }
                    $exclude = [];
                    if (!$options['offline']) {
                        $exclude[] = $e->literal(Offline::FACTORY_NAME);
                    }
                    if (!$options['credit']) {
                        $exclude[] = $e->literal(Credit::FACTORY_NAME);
                    }
                    if (!$options['outstanding']) {
                        $exclude[] = $e->literal(Outstanding::FACTORY_NAME);
                    }
                    if (!empty($exclude)) {
                        $qb->andWhere($e->notIn('m.factoryName', $exclude));
                    }
                }

                return $qb;
            };
        };

        $resolver
            ->setDefaults([
                'label'             => 'ekyna_commerce.payment_method.label.singular',
                'enabled'           => false, // Whether to exclude disabled factories
                'available'         => false, // Whether to exclude unavailable factories
                'offline'           => true,  // Whether to include offline factories
                'credit'            => true,  // Whether to include credit factory
                'outstanding'       => true,  // Whether to include outstanding factory
                'class'             => $this->dataClass,
                'query_builder'     => $queryBuilder,
                'invoice'           => null,
                'preferred_choices' => function (Options $options, $value) {
                    return $this->getPreferredChoices($options, $value);
                },
            ])
            ->setAllowedTypes('enabled', 'bool')
            ->setAllowedTypes('available', 'bool')
            ->setAllowedTypes('offline', 'bool')
            ->setAllowedTypes('credit', 'bool')
            ->setAllowedTypes('outstanding', 'bool')
            ->setAllowedTypes('invoice', ['null', InvoiceInterface::class])
            ->setNormalizer('enabled', function (Options $options, $value) {
                if ($options['disabled'] || $options['available']) {
                    return true;
                }

                return $value;
            })
            ->setNormalizer('placeholder', function (Options $options, $value) {
                if (empty($value) && !$options['required']) {
                    $value = 'ekyna_core.value.none';
                }

                return $value;
            });
    }

    /**
     * Returns the preferred choices.
     *
     * @param Options $options
     * @param mixed   $value
     *
     * @return array
     */
    private function getPreferredChoices(Options $options, $value)
    {
        if (!empty($value)) {
            return $value;
        }

        /** @var InvoiceInterface $invoice */
        if (null === $invoice = $options['invoice']) {
            return [];
        }

        $sale = $invoice->getSale();
        if (!$sale instanceof OrderInterface) {
            return [];
        }

        $methodIds = [];
        $preferredChoices = [];
        $states = [PaymentStates::STATE_CAPTURED, PaymentStates::STATE_AUTHORIZED, PaymentStates::STATE_REFUNDED];
        foreach ($sale->getPayments() as $payment) {
            $method = $payment->getMethod();
            if (in_array($payment->getState(), $states, true)) {
                $preferredChoices[] = $method;
                break;
            } elseif ($method->isManual() && $payment->getState() === PaymentStates::STATE_PENDING) {
                if (!in_array($method->getId(), $methodIds, true)) {
                    $preferredChoices[] = $method;
                    $methodIds[] = $method->getId();
                }
            }
        }

        return $preferredChoices;
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_payment_method_choice';
    }
}
