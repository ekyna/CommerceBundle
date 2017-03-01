<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TaxGroupType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxGroupType extends ResourceFormType
{
    /**
     * @var string
     */
    private $taxRuleClass;


    /**
     * Constructor.
     *
     * @param string $taxGroupClass
     * @param string $taxRuleClass
     */
    public function __construct($taxGroupClass, $taxRuleClass)
    {
        parent::__construct($taxGroupClass);

        $this->taxRuleClass = $taxRuleClass;
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
            ->add('default', Type\CheckboxType::class, [
                'label'    => 'ekyna_core.field.default',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('taxRules', ResourceType::class, [
                'label'    => 'ekyna_commerce.tax_rule.label.plural',
                'class'    => $this->taxRuleClass,
                'multiple' => true,
                'required' => false,
            ]);
    }
}
