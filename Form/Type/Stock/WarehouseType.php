<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Stock;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class WarehouseType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WarehouseType extends ResourceFormType
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;


    /**
     * Constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param string                        $dataClass
     */
    public function __construct(AuthorizationCheckerInterface $authorization, string $dataClass)
    {
        parent::__construct($dataClass);

        $this->authorization = $authorization;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => 'ekyna_core.field.name',
            ])
            ->add('countries', CountryChoiceType::class, [
                'label'    => 'ekyna_commerce.country.label.plural',
                'enabled'  => false,
                'multiple' => true,
            ])
            ->add('office', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.warehouse.field.office',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('default', Type\CheckboxType::class, [
                'label'    => 'ekyna_core.field.default',
                'required' => false,
                // TODO Remove when IsDefault resource behavior will be implemented
                'disabled' => !$this->authorization->isGranted('ROLE_SUPER_ADMIN'),
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('enabled', Type\CheckboxType::class, [
                'label'    => 'ekyna_core.field.enabled',
                'required' => false,
                'disabled' => !$this->authorization->isGranted('ROLE_SUPER_ADMIN'),
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('priority', Type\IntegerType::class, [
                'label' => 'ekyna_core.field.priority',
            ])
            ->add('address', AddressType::class, [
                'label'        => false,
                'inherit_data' => true,
            ]);
    }
}
