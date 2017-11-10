<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InvoiceLineType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceLineType extends ResourceFormType
{
    /**
     * @var InvoiceCalculatorInterface
     */
    private $calculator;


    /**
     * Constructor.
     *
     * @param InvoiceCalculatorInterface $calculator
     * @param string                     $class
     */
    public function __construct(InvoiceCalculatorInterface $calculator, $class)
    {
        parent::__construct($class);

        $this->calculator = $calculator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('quantity', Type\NumberType::class, [
            'label'          => 'ekyna_core.field.quantity',
            'attr'           => [
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

        if ($options['type'] === InvoiceTypes::TYPE_INVOICE) {
            $max = $this->calculator->calculateInvoiceableQuantity($line);
        } elseif ($options['type'] === InvoiceTypes::TYPE_CREDIT) {
            $max = $this->calculator->calculateCreditableQuantity($line);
        } else {
            throw new InvalidArgumentException("Unexpected invoice type.");
        }

        $view->vars['max_quantity'] = $max;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'type' => null,
            ])
            ->setAllowedTypes('type', 'string')
            ->setAllowedValues('type', InvoiceTypes::getTypes());
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_invoice_line';
    }
}
