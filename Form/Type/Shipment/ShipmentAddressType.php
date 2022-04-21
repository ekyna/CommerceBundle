<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\ArrayAddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ShipmentAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['admin_mode']) {
            return;
        }

        $builder->add('information', TextareaType::class, [
            'label'    => t('field.information', [], 'EkynaUi'),
            'required' => false,
        ]);
    }

    public function getParent(): ?string
    {
        return ArrayAddressType::class;
    }
}
