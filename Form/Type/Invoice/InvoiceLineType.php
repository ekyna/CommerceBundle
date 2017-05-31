<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineTypes;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Invoice\Util\InvoiceUtil;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class InvoiceLineType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceLineType extends ResourceFormType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('quantity', Type\NumberType::class, [
            'label' => 'ekyna_core.field.quantity',
            'attr' => [
                'class' => 'input-sm',
            ],
            'error_bubbling' => true,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var InvoiceLineInterface $line */
        $line = $form->getData();

        $view->vars['designation'] = $line->getDesignation();
        $view->vars['description'] = $line->getDescription();
        $view->vars['reference'] = $line->getReference();

        if ($line->getType() === InvoiceLineTypes::TYPE_GOOD) {
            $invoice = $line->getInvoice();
            if ($invoice->getType() === InvoiceTypes::TYPE_INVOICE) {
                $max = InvoiceUtil::calculateMaxInvoiceQuantity($line);
            } elseif ($invoice->getType() === InvoiceTypes::TYPE_CREDIT) {
                $max = InvoiceUtil::calculateMaxCreditQuantity($line);
            } else {
                throw new InvalidArgumentException("Unexpected invoice type.");
            }
        } else {
            $max = 1;
        }

        $view->vars['max_quantity'] = $max;
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_invoice_line';
    }
}
