<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Manufacture;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Component\Commerce\Manufacture\Calculator\ProductionCalculator;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class ProductionOrderType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Manufacture
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionType extends AbstractResourceType
{
    public function __construct(
        private readonly ProductionCalculator $calculator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            $production = $event->getData();

            if (!$production instanceof ProductionInterface) {
                throw new UnexpectedTypeException($production, ProductionInterface::class);
            }

            $max = $this->calculator->calculateMaxQuantity($production);

            $event
                ->getForm()
                ->add('quantity', IntegerType::class, [
                    'label'    => t('field.quantity', [], 'EkynaUi'),
                    'attr' => [
                        'min' => 1,
                        'max' => $max,
                    ],
                ]);
        });
    }
}
