<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\EventListener;

use Ekyna\Bundle\CommerceBundle\Form\FormHelper;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\PriceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxGroupChoiceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionPositionType;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class SaleItemTypeSubscriber
 * @package Ekyna\Bundle\CommerceBundle\Form\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemTypeSubscriber implements EventSubscriberInterface
{
    private string $currency;

    public function __construct(string $currency)
    {
        $this->currency = $currency;
    }

    public function onPreSetData(FormEvent $event): void
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

        FormHelper::addQuantityType($form, $item->getUnit(), [
            'disabled'       => $hasParent,
            'attr'           => [
                'placeholder' => t('field.quantity', [], 'EkynaUi'),
                'min'         => 1,
            ],
            'error_bubbling' => true,
        ]);

        $form
            ->add('designation', Type\TextType::class, [
                'label'          => t('field.designation', [], 'EkynaUi'),
                'disabled'       => $hasSubject && !$item->isCompound(),
                'attr'           => [
                    'placeholder' => t('field.designation', [], 'EkynaUi'),
                ],
                'error_bubbling' => true,
            ])
            ->add('reference', Type\TextType::class, [
                'label'          => t('field.reference', [], 'EkynaUi'),
                'disabled'       => $hasSubject,
                'attr'           => [
                    'placeholder' => t('field.reference', [], 'EkynaUi'),
                ],
                'error_bubbling' => true,
            ])
            ->add('weight', Type\NumberType::class, [
                'label'          => t('field.weight', [], 'EkynaUi'),// TODO unit weight ?
                'decimal'        => true,
                'scale'          => 3,
                'disabled'       => $item->isCompound(),
                'attr'           => [
                    'placeholder' => t('field.weight', [], 'EkynaUi'),
                    'input_group' => ['append' => 'kg'],
                    'min'         => 0,
                ],
                'error_bubbling' => true,
            ])
            ->add('netPrice', PriceType::class, [
                'label'          => t('sale.field.net_unit', [], 'EkynaCommerce'),
                'currency'       => $this->currency,
                'disabled'       => $item->isCompound(),
                'attr'           => [
                    'placeholder' => t('sale.field.net_unit', [], 'EkynaCommerce'),
                ],
                'error_bubbling' => true,
            ])
            ->add('taxGroup', TaxGroupChoiceType::class, [
                'disabled'       => $hasChildren || $hasParent || $hasSubject,
                'attr'           => [
                    'placeholder' => t('field.tax_group', [], 'EkynaCommerce'),
                ],
                'error_bubbling' => true,
            ])
            ->add('private', Type\CheckboxType::class, [
                'label'    => t('field.private', [], 'EkynaCommerce'),
                'disabled' => $item->hasPublicChildren(),
                'required' => false,
            ])
            ->add('position', CollectionPositionType::class, []);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => ['onPreSetData', 0],
        ];
    }
}
