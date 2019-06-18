<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Customer\Balance\Balance;
use Ekyna\Component\Commerce\Customer\Balance\Line;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class BalanceNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BalanceNormalizer implements NormalizerInterface
{
    use FormatterAwareTrait;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var string
     */
    protected $currency;


    /**
     * Constructor.
     *
     * @param TranslatorInterface   $translator
     * @param UrlGeneratorInterface $urlGenerator
     * @param string                $currency
     */
    public function __construct(
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        string $currency
    ) {
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->currency = $currency;
    }

    /**
     * @inheritDoc
     *
     * @param Balance $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $formatter = $this->getFormatter();

        $translations = [
            Line::TYPE_INVOICE => $this->translator->trans('ekyna_commerce.invoice.label.singular'),
            Line::TYPE_CREDIT  => $this->translator->trans('ekyna_commerce.credit.label.singular'),
            Line::TYPE_PAYMENT => $this->translator->trans('ekyna_commerce.payment.label.singular'),
            Line::TYPE_REFUND  => $this->translator->trans('ekyna_commerce.refund.label.singular'),
        ];

        $date = function (\DateTime $date = null) {
            return $date ? $date->format(DateUtil::DATE_FORMAT) : null;
        };

        $fCurrency = function (float $amount) use ($formatter) {
            return 0 !== Money::compare(0, $amount, $this->currency)
                ? $formatter->currency($amount, $this->currency)
                : null;
        };

        $fDate = function (\DateTime $date = null) use ($formatter) {
            return $date ? $formatter->date($date) : null;
        };

        $data = [];
        $lines = [];

        if ($format === 'json') {
            // Balance data
            $data = [
                'diff'      => $object->getDiff(),
                'filter'    => $object->getFilter(),
                'debit'     => $object->getDebit(),
                'credit'    => $object->getCredit(),
                'from'      => $date($object->getFrom()),
                'to'        => $date($object->getTo()),
                'formatted' => [
                    'diff'   => $fCurrency($object->getDiff()),
                    'debit'  => $fCurrency($object->getDebit()),
                    'credit' => $fCurrency($object->getCredit()),
                ],
            ];
        } elseif ($format === 'csv') {
            $lines[] = [
                'date'           => 'Date',
                'number'         => 'Number',
                'label'          => 'Label',
                'order_number'   => 'Order number',
                'voucher_number' => 'Voucher number',
                'order_date'     => 'Order date',
                'due_date'       => 'Due date',
                'due'            => 'Is due',
                'debit'          => 'Debit',
                'credit'         => 'Credit',
            ];
        }

        // Carry forwards line
        if (0 < $object->getDebitForward() || 0 < $object->getCreditForward()) {
            $datum = [
                'date'           => $date($object->getFrom()),
                'number'         => null,
                'label'          => $this->translator->trans('ekyna_commerce.customer.balance.forward'),
                'order_number'   => null,
                'voucher_number' => null,
                'order_date'     => null,
                'due_date'       => null,
                'due'            => false,
                'debit'          => $object->getDebitForward(),
                'credit'         => $object->getCreditForward(),
            ];

            if ($format === 'json') {
                $datum['type'] = Line::TYPE_FORWARD;
                $datum['order_url'] = null;
                $datum['formatted'] = [
                    'date'       => $fDate($object->getFrom()),
                    'due_date'   => null,
                    'order_date' => null,
                    'debit'      => $fCurrency($object->getDebitForward()),
                    'credit'     => $fCurrency($object->getCreditForward()),
                ];
            }

            $lines[] = $datum;
        }

        // Lines
        foreach ($object->getLines() as $line) {
            $datum = [
                'date'           => $date($line->getDate()),
                'number'         => $line->getNumber(),
                'label'          => $translations[$line->getType()],
                'order_number'   => $line->getOrderNumber(),
                'voucher_number' => $line->getVoucherNumber(),
                'order_date'     => $date($line->getOrderDate()),
                'due_date'       => $date($line->getDueDate()),
                'due'            => $line->isDue(),
                'debit'          => $line->getDebit(),
                'credit'         => $line->getCredit(),
            ];

            if ($format === 'json') {
                $datum['type'] = $line->getType();

                if ($object->isPublic()) {
                    $url = $this->urlGenerator->generate('ekyna_commerce_account_order_show', [
                        'number' => $line->getOrderNumber(),
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                } else {
                    $url = $this->urlGenerator->generate('ekyna_commerce_order_admin_show', [
                        'orderId' => $line->getOrderId(),
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                }
                $datum['order_url'] = $url;

                $datum['formatted'] = [
                    'date'       => $fDate($line->getDate()),
                    'order_date' => $fDate($line->getOrderDate()),
                    'due_date'   => $fDate($line->getDueDate()),
                    'debit'      => $fCurrency($line->getDebit()),
                    'credit'     => $fCurrency($line->getCredit()),
                ];
            }

            $lines[] = $datum;
        }

        if ($format !== 'json') {
            return $lines;
        }

        $data['lines'] = $lines;

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Balance;
    }
}
