<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TaxRuleType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxRuleType extends ResourceFormType
{
    /**
     * @var string
     */
    private $taxClass;

    /**
     * @var string
     */
    private $customerGroupClass;


    /**
     * Constructor.
     *
     * @param string $taxRuleClass
     * @param string $taxClass
     * @param string $customerGroupClass
     */
    public function __construct($taxRuleClass, $taxClass, $customerGroupClass)
    {
        parent::__construct($taxRuleClass);

        $this->taxClass = $taxClass;
        $this->customerGroupClass = $customerGroupClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => 'ekyna_core.field.name',
            ])
            ->add('priority', Type\NumberType::class, [
                'label' => 'ekyna_core.field.priority',
            ])
            ->add('taxGroups', TaxGroupChoiceType::class, [
                'label'     => 'ekyna_commerce.tax_group.label.plural',
                'multiple'  => true,
                'allow_new' => true,
            ])
            ->add('taxes', ResourceType::class, [
                'label'     => 'ekyna_commerce.tax.label.plural',
                'class'     => $this->taxClass,
                'multiple'  => true,
                'allow_new' => true,
            ])
            ->add('customerGroups', ResourceType::class, [
                'label'     => 'ekyna_commerce.customer_group.label.plural',
                'class'     => $this->customerGroupClass,
                'multiple'  => true,
                'allow_new' => true,
            ]);
    }
}
