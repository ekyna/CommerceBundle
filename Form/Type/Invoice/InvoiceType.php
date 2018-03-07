<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentMethodChoiceType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Builder\InvoiceBuilderInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InvoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceType extends ResourceFormType
{
    /**
     * @var InvoiceBuilderInterface
     */
    private $builder;


    /**
     * Constructor.
     *
     * @param InvoiceBuilderInterface $builder
     * @param string                  $dataClass
     */
    public function __construct(InvoiceBuilderInterface $builder, $dataClass)
    {
        parent::__construct($dataClass);

        $this->builder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', Type\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'required' => false,
                'disabled' => true,
            ])
            ->add('createdAt', Type\DateTimeType::class, [
                'label'    => 'ekyna_core.field.date',
                'required' => false,
                'empty_data' => (new \DateTime())->format('d/m/Y H:i') // TODO Use the proper format !
            ])
            ->add('comment', Type\TextareaType::class, [
                'label'    => 'ekyna_core.field.comment',
                'required' => false,
            ])
            ->add('description', Type\TextareaType::class, [
                'label'    => 'ekyna_commerce.field.description',
                'required' => false,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface $invoice */
                $invoice = $event->getData();

                if (null === $sale = $invoice->getSale()) {
                    throw new RuntimeException("The invoice must be associated with a sale at this point.");
                }
                if (!$sale instanceof OrderInterface) {
                    throw new RuntimeException("Not yet supported.");
                };

                $disabledLines = true;
                if (null === $invoice->getShipment()) {
                    $this->builder->build($invoice);
                    $disabledLines = false;
                }

                $form->add('lines', InvoiceTreeType::class, [
                    'invoice'    => $invoice,
                    'entry_type' => $options['line_type'],
                    'disabled'   => $disabledLines,
                ]);

                if (InvoiceTypes::isCredit($invoice)) {
                    $required = false;
                    $states = [
                        PaymentStates::STATE_CAPTURED,
                        PaymentStates::STATE_AUTHORIZED,
                        PaymentStates::STATE_REFUNDED,
                    ];
                    foreach ($sale->getPayments() as $payment) {
                        // Ignore outstanding payments
                        if ($payment->getMethod()->isOutstanding()) {
                            continue;
                        }
                        if (in_array($payment->getState(), $states, true)) {
                            $required = true;
                            break;
                        }
                    }

                    $form->add('paymentMethod', PaymentMethodChoiceType::class, [
                        'label'       => 'ekyna_commerce.invoice.field.payment_method',
                        'required'    => $required,
                        'invoice'     => $invoice,
                        'outstanding' => false,
                    ]);
                }
            });
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface $invoice */
        $invoice = $form->getData();

        $view->vars['credit_mode'] = InvoiceTypes::isCredit($invoice->getType());

        FormUtil::addClass($view, 'invoice');
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['line_type'])
            ->setAllowedTypes('line_type', 'string');
    }
}
