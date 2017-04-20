<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Constants as Credit;
use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants as Offline;
use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants as Outstanding;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function in_array;

/**
 * Class PaymentMethodChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $queryBuilder = function (Options $options) {
            return function (EntityRepository $repository) use ($options): QueryBuilder {
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
                    if ($options['public']) {
                        $qb->andWhere($e->eq('m.private', true));
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
                'enabled'           => false, // Whether to exclude disabled methods
                'available'         => false, // Whether to exclude unavailable methods
                'public'            => true,  // Whether to exclude private methods
                'offline'           => true,  // Whether to include offline factories
                'credit'            => true,  // Whether to include credit factory
                'outstanding'       => true,  // Whether to include outstanding factory
                'resource'          => 'ekyna_commerce.payment_method',
                'query_builder'     => $queryBuilder,
                'invoice'           => null,
                'preferred_choices' => function (Options $options, $value): array {
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
            });
    }

    /**
     * Returns the preferred choices.
     */
    private function getPreferredChoices(Options $options, $value): array
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
        foreach ($sale->getPayments(true) as $payment) {
            $method = $payment->getMethod();
            if (PaymentStates::isPaidState($payment, true)) {
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

    public function getParent(): ?string
    {
        return ResourceChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_payment_method_choice';
    }
}
