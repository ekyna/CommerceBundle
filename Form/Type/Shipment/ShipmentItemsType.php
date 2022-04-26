<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class ShipmentItemsType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentItemsType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['headers'] = false;
        $view->vars['with_availability'] = $view->parent->vars['with_availability'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['entry_type'])
            ->setDefaults([
                'label' => t('shipment.field.items', [], 'EkynaCommerce'),
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_shipment_items';
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}
