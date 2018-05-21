<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

/**
 * Class SaleQuantitiesType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleQuantitiesType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                /**
                 * Model data.
                 * @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale
                 */
                $sale = $event->getData();
                $form = $event->getForm();

                $createItemQuantityForm = function (SaleItemInterface $item, $path = 'items') use ($form, $options, &$createItemQuantityForm) {
                    if (!$item->isImmutable()) {
                        $constraints = [
                            new Constraints\NotBlank(),
                            new Constraints\GreaterThanOrEqual(['value' => 1]),
                        ];
                        $form->add('item_' . $item->getId(), IntegerType::class, [
                            'label'         => false,
                            'property_path' => $path . '[' . $item->getId() . '].quantity',
                            'attr'          => [
                                'min' => 1,
                            ],
                            'constraints'   => $constraints,
                        ]);
                    }
                    foreach ($item->getChildren() as $child) {
                        $createItemQuantityForm($child, $path . '[' . $item->getId() . '].children');
                    }
                };

                foreach ($sale->getItems() as $item) {
                    $createItemQuantityForm($item);
                }
            });
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'        => SaleInterface::class,
                'validation_groups' => ['Calculation'],
            ]);
    }
}
