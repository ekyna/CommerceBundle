<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Decimal\Decimal;
use Ekyna\Bundle\CommerceBundle\Form\FormHelper;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Commerce\Invoice\Resolver\AvailabilityResolverFactory;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InvoiceLineType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceLineType extends AbstractResourceType
{
    private AvailabilityResolverFactory $availabilityResolverFactory;

    public function __construct(AvailabilityResolverFactory $availabilityResolverFactory)
    {
        $this->availabilityResolverFactory = $availabilityResolverFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            /** @var InvoiceLineInterface $line */
            $line = $event->getData();

            $unit = Units::PIECE;
            $disabled = $options['disabled'] || $line->isQuantityLocked();
            if ($saleItem = $line->getSaleItem()) {
                $unit = $saleItem->getUnit();
            }

            FormHelper::addQuantityType($event->getForm(), $unit, [
                'disabled'       => $disabled,
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'input-sm',
                ],
            ]);

            $event
                ->getForm()
                ->add('children', InvoiceLinesType::class, [
                    'entry_type'    => static::class,
                    'entry_options' => [
                        'invoice'  => $options['invoice'],
                        'level'    => $options['level'] + 1,
                        'disabled' => $disabled,
                    ],
                ]);
        });
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var InvoiceLineInterface $line */
        $line = $form->getData();
        /** @var InvoiceInterface $invoice */
        $invoice = $options['invoice'];
        $unit = Units::PIECE;
        $availability = $line->getAvailability();

        $resolver = $this->availabilityResolverFactory->createWithInvoice($invoice);

        if ($saleItem = $line->getSaleItem()) {
            $unit = $saleItem->getUnit();
            if (!$availability) {
                $availability = $resolver->resolveSaleItem($saleItem);
            }
        } elseif ($adjustment = $line->getSaleAdjustment()) {
            if (!$availability) {
                $availability = $resolver->resolveSaleDiscount($adjustment);
            }
        } elseif ($sale = $line->getSale()) {
            if (!$availability) {
                $availability = $resolver->resolveSaleShipment($sale);
            }
        } else {
            throw new RuntimeException();
        }

        $view->vars['line'] = $line;
        $view->vars['availability'] = $availability;
        $view->vars['level'] = $options['level'];
        $view->vars['credit_mode'] = $invoice->isCredit();

        $quantity = $view->children['quantity'];
        $quantity->vars['attr']['data-max'] = Units::fixed($availability->getMaximum() ?: new Decimal(0), $unit);

        if ($saleItem && isset($view->parent->parent->children['quantity'])) {
            $quantity->vars['attr']['data-quantity'] = Units::fixed($saleItem->getQuantity(), $unit);
            $quantity->vars['attr']['data-parent'] = $view->parent->parent->children['quantity']->vars['id'];
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'level'   => 0,
                'invoice' => null,
            ])
            ->setAllowedTypes('level', 'int')
            ->setAllowedTypes('invoice', InvoiceInterface::class);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_invoice_line';
    }
}
