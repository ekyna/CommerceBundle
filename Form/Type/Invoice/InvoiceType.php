<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilderInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class InvoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceType extends ResourceFormType
{
    /**
     * @var string
     */
    private $lineClass;

    /**
     * @var DocumentBuilderInterface
     */
    private $invoiceBuilder;


    /**
     * Constructor.
     *
     * @param string $dataClass
     * @param string $itemClass
     */
    public function __construct($dataClass, $itemClass)
    {
        parent::__construct($dataClass);

        $this->lineClass = $itemClass;
    }

    /**
     * Sets the invoice builder.
     *
     * @param DocumentBuilderInterface $invoiceBuilder
     */
    public function setInvoiceBuilder(DocumentBuilderInterface $invoiceBuilder)
    {
        $this->invoiceBuilder = $invoiceBuilder;
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
            ->add('description', Type\TextareaType::class, [
                'label'    => 'ekyna_core.field.description',
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface $invoice */
            $invoice = $event->getData();

            if (null === $sale = $invoice->getSale()) {
                throw new RuntimeException("The invoice must be associated with a sale at this point.");
            }
            if (!$sale instanceof OrderInterface) {
                throw new RuntimeException("Not yet supported.");
            }

            if (0 === $invoice->getLines()->count()) {
                $this->invoiceBuilder->build($invoice);
            }

            $event->getForm()->add('lines', InvoiceLinesType::class, [
                'label'         => 'ekyna_commerce.invoice.field.lines',
                'entry_options' => [
                    'type'       => $invoice->getType(),
                    'data_class' => $this->lineClass,
                ],
            ]);
        });
    }
}
