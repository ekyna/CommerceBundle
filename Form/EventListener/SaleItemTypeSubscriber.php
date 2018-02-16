<?php

namespace Ekyna\Bundle\CommerceBundle\Form\EventListener;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
//use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AdjustmentsType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxGroupChoiceType;
//use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemsType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionPositionType;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class SaleItemTypeSubscriber
 * @package Ekyna\Bundle\CommerceBundle\Form\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemTypeSubscriber implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    private $addCollections;

    /**
     * @var string
     */
    private $itemType;

    /**
     * @var string
     */
    private $adjustmentType;

    /**
     * @var string
     */
    private $currency;


    /**
     * Constructor.
     *
     * @param bool   $addCollections
     * @param string $itemType
     * @param string $adjustmentType
     * @param string $currency
     */
    public function __construct($addCollections, $itemType, $adjustmentType, $currency)
    {
        $this->addCollections = (bool)$addCollections;
        $this->itemType = $itemType;
        $this->adjustmentType = $adjustmentType;
        $this->currency = $currency;
    }

    /**
     * Form pre set data event handler.
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $item = $event->getData();

        $hasParent = false;
        $hasChildren = false;
        $hasSubject = false;
        if ($item instanceof SaleItemInterface) {
            $hasParent = null !== $item->getParent();
            $hasChildren = $item->hasChildren();
            $hasSubject = $item->hasSubjectIdentity();
        }

        $form
            ->add('designation', Type\TextType::class, [
                'label' => 'ekyna_core.field.designation',
                'disabled' => $hasSubject && !$item->isCompound(),
                'attr'  => [
                    'placeholder' => 'ekyna_core.field.designation',
                ],
            ])
            ->add('reference', Type\TextType::class, [
                'label' => 'ekyna_core.field.reference',
                'disabled' => $hasSubject,
                'attr'  => [
                    'placeholder' => 'ekyna_core.field.reference',
                ],
            ])
            ->add('weight', Type\NumberType::class, [
                'label'    => 'ekyna_core.field.weight', // TODO unit weight ?
                'scale'    => 3,
                'required' => false,
                'disabled' => $item->isCompound(),
                'attr'     => [
                    'placeholder' => 'ekyna_core.field.weight',
                    'input_group' => ['append' => 'kg'],
                    'min'         => 0,
                ],
            ])
            ->add('netPrice', MoneyType::class, [
                'label'    => 'ekyna_commerce.sale.field.net_unit',
                'currency' => $this->currency,
                'required' => false,
                'disabled' => $item->isCompound(),
                'attr'     => [
                    'placeholder' => 'ekyna_commerce.sale.field.net_unit',
                    //'input_group' => ['append' => '€'],  // TODO sale currency
                ],
            ])
            ->add('taxGroup', TaxGroupChoiceType::class, [
                'label'    => 'ekyna_commerce.sale_item.field.tax_group',
                'required' => false,
                'disabled' => $hasChildren || $hasParent || $hasSubject,
                'select2'  => false,
                'attr'     => [
                    'placeholder' => 'ekyna_commerce.sale_item.field.tax_group',
                ],
            ])
            ->add('quantity', Type\IntegerType::class, [
                'label'    => 'ekyna_core.field.quantity',
                'disabled' => $hasParent,
                'attr'     => [
                    'placeholder' => 'ekyna_core.field.quantity',
                    'min'         => 1,
                ],
            ])
            ->add('private', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.sale_item.field.private',
                'disabled' => $item->hasPublicChildren(),
                'required' => false,
            ])
            ->add('position', CollectionPositionType::class, []);

        /* TODO Remove if (!$hasSubject) {
            if ($this->addCollections) {
                $form->add('items', SaleItemsType::class, [
                    'property_path' => 'children',
                    'children_mode' => true,
                    'entry_type'    => $this->itemType,
                    'entry_options' => [
                        'label'            => false,
                        'with_collections' => false,
                        'currency'         => $this->currency,
                    ],
                ]);
            }

            $form->add('adjustments', AdjustmentsType::class, [
                'prototype_name'        => '__item_adjustment__',
                'entry_type'            => $this->adjustmentType,
                'add_button_text'       => 'ekyna_commerce.sale.form.add_item_adjustment',
                'delete_button_confirm' => 'ekyna_commerce.sale.form.remove_item_adjustment',
            ]);
        }*/
    }

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => ['onPreSetData', 0],
        ];
    }
}
