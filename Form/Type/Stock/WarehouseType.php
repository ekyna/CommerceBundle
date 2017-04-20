<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Stock;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use function Symfony\Component\Translation\t;

/**
 * Class WarehouseType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WarehouseType extends AbstractResourceType
{
    private AuthorizationCheckerInterface $authorization;

    public function __construct(AuthorizationCheckerInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => t('field.name', [], 'EkynaUi'),
            ])
            ->add('countries', CountryChoiceType::class, [
                'enabled'  => false,
                'multiple' => true,
            ])
            ->add('office', Type\CheckboxType::class, [
                'label'    => t('warehouse.field.office', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('default', Type\CheckboxType::class, [
                'label'    => t('field.default', [], 'EkynaUi'),
                'required' => false,
                // TODO Remove when resource IsDefaultBehavior will be implemented
                'disabled' => !$this->authorization->isGranted('ROLE_SUPER_ADMIN'),
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('enabled', Type\CheckboxType::class, [
                'label'    => t('field.enabled', [], 'EkynaUi'),
                'required' => false,
                'disabled' => !$this->authorization->isGranted('ROLE_SUPER_ADMIN'),
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('priority', Type\IntegerType::class, [
                'label' => t('field.priority', [], 'EkynaUi'),
            ])
            ->add('address', AddressType::class, [
                'label'        => false,
                'inherit_data' => true,
            ]);
    }
}
