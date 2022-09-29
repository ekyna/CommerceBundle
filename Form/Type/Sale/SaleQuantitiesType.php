<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

use function Symfony\Component\Translation\t;

/**
 * Class SaleQuantitiesType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleQuantitiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
                /** @var SaleInterface $sale */
                $sale = $event->getData();
                $form = $event->getForm();

                $createItemQuantityForm =
                    function (SaleItemInterface $item, $path = 'items')
                    use ($form, $options, &$createItemQuantityForm): void {
                        if (!$item->isImmutable()) {
                            $constraints = [
                                new Constraints\NotBlank(),
                                new Constraints\GreaterThanOrEqual(['value' => 1]),
                            ];

                            $form->add('item_' . $item->getId(), IntegerType::class, [
                                'label'         => false,
                                'decimal'       => true,
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

        $builder->add('recalculate', SubmitType::class, [
            'label' => t('button.recalculate', [], 'EkynaUi'),
            'attr'  => [
                'class' => 'btn-sm',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class'        => SaleInterface::class,
                'validation_groups' => ['Calculation'],
            ]);
    }
}
