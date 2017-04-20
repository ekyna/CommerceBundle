<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Bundle\CommerceBundle\Form\EventListener\SaleItemTypeSubscriber;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SaleItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $subscriber = new SaleItemTypeSubscriber($options['currency']);

        $builder->addEventSubscriber($subscriber);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['with_collections'] = $options['with_collections'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                // TODO Remove useless options
                'with_collections'     => true,
                'item_type'            => null,
                'item_adjustment_type' => null,
                'currency'             => null,
                'error_bubbling'       => false,
            ])
            ->setAllowedTypes('with_collections', 'bool')
            ->setAllowedTypes('item_type', 'string')
            ->setAllowedTypes('currency', 'string')
            ->setAllowedTypes('item_adjustment_type', 'string');
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_sale_item';
    }
}
