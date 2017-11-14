<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InvoiceLinesType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceLinesType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var \Doctrine\Common\Collections\Collection $items */
            $items = $event->getData();

            /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface $item */
            foreach ($items as $item) {
                if (0 == $item->getQuantity()) {
                    $items->removeElement($item);
                    $item->setInvoice(null);
                }
            }

            //$event->getForm()->setData($items);
            $event->setData($items);
        }, 51); // Before collection type's submit event listener
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type' => InvoiceLineType::class,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_invoice_lines';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
