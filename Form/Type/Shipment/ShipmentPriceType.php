<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Decimal\Decimal;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\HiddenResourceType;
use Ekyna\Component\Commerce\Shipment\Entity\ShipmentPrice;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentZoneInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_merge;
use function is_null;
use function Symfony\Component\Translation\t;

/**
 * Class ShipmentPriceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPriceType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('weight', NumberType::class, [
                'label'      => t('field.weight', [], 'EkynaUi'),
                'decimal'    => true,
                'scale'      => 3,
                'empty_data' => new Decimal(0),
                'attr'       => [
                    'placeholder'  => 'field.weight',
                    'input_group'  => ['append' => 'kg'],
                    'min'          => 0,
                    'autocomplete' => 'off',
                ],
            ])
            ->add('netPrice', NumberType::class, [
                'label'      => t('field.net_price', [], 'EkynaCommerce'),
                'decimal'    => true,
                'scale'      => 5,
                'empty_data' => new Decimal(0),
                'attr'       => [
                    'placeholder'  => 'field.net_price',
                    'input_group'  => ['append' => '€'],  // TODO by currency
                    'autocomplete' => 'off',
                ],
            ]);

        if ('zone' === $options['filter_by']) {
            $builder->add('zone', HiddenResourceType::class, [
                'resource' => ShipmentZoneInterface::class,
                'attr'     => [
                    'class' => 'shipment-price-zone',
                ],
            ]);

            return;
        }

        if ('method' === $options['filter_by']) {
            $builder->add('method', HiddenResourceType::class, [
                'resource' => ShipmentMethodInterface::class,
                'attr'     => [
                    'class' => 'shipment-price-method',
                ],
            ]);
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (null === $filterBy = $options['filter_by']) {
            return;
        }

        /** @var ShipmentPrice $price */
        if (!$price = $form->getData()) {
            return;
        }

        if ($filterBy == 'method') {
            $filterValue = $price->getMethod();
        } else {
            $filterValue = $price->getZone();
        }

        $view->vars['attr'] = array_merge($view->vars['attr'], [
            'data-' . $filterBy => is_null($filterValue) ? 'null' : $filterValue->getId(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('filter_by', null)
            ->setAllowedValues('filter_by', [null, 'zone', 'method']);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_shipment_price';
    }
}
