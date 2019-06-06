<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
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
     * Constructor.
     *
     * @param TranslatorInterface   $translator
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(TranslatorInterface $translator, UrlGeneratorInterface $urlGenerator)
    {
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
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

        $data = [
            'debit'    => $formatter->currency($object->getDebit(), 'EUR'),
            'credit'   => $formatter->currency($object->getCredit(), 'EUR'),
            'diff'     => $formatter->currency($object->getDiff(), 'EUR'),
            'not_done' => $object->isNotDone(),
            'lines'    => [],
        ];

        foreach ($object->getLines() as $line) {
            $datum = [
                'date'         => $formatter->date($line->getDate()),
                'number'       => $line->getNumber(),
                'label'        => $translations[$line->getType()],
                'order_number' => $line->getOrderNumber(),
                'order_date'   => $formatter->date($line->getOrderDate()),
                'due_date'     => $line->getDueDate() ? $formatter->date($line->getDueDate()) : null,
                'done'         => $line->isDone(),
            ];

            if ($format === 'json') {
                $datum['debit_raw'] = $line->getDebit();
                $datum['credit_raw'] = $line->getCredit();

                $datum['debit'] = 0 < $line->getDebit() ? $formatter->currency($line->getDebit(), 'EUR') : null;
                $datum['credit'] = 0 < $line->getCredit() ? $formatter->currency($line->getCredit(), 'EUR') : null;

                if ($object->isPublic()) {
                    $url = $this->urlGenerator->generate('ekyna_commerce_account_order_show', [
                        'number' => $line->getOrderNumber(),
                    ]);
                } else {
                    $url = $this->urlGenerator->generate('ekyna_commerce_order_admin_show', [
                        'orderId' => $line->getOrderId(),
                    ]);
                }

                $datum['type'] = $line->getType();
                $datum['order_url'] = $url;
            } else {
                $datum['debit'] = 0 < $line->getDebit() ? $line->getDebit() : null;
                $datum['credit'] = 0 < $line->getCredit() ? $line->getCredit() : null;
            }

            $data['lines'][] = $datum;
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Balance && in_array($format, ['json', 'csv']);
    }
}
