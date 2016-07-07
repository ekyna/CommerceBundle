<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TaxType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxType extends ResourceFormType
{
    /**
     * @var string
     */
    private $countryClass;

    /**
     * @var string
     */
    private $stateClass;


    /**
     * Constructor.
     *
     * @param string $taxClass
     * @param string $countryClass
     * @param string $stateClass
     */
    public function __construct($taxClass, $countryClass, $stateClass = null)
    {
        parent::__construct($taxClass);

        $this->countryClass = $countryClass;
        $this->stateClass = $stateClass;
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
            ->add('rate', Type\NumberType::class, [
                'label' => 'ekyna_core.field.rate',
                'attr' => [
                    'input_group' => ['append' => '%'],
                ],
            ])
            ->add('country', ResourceType::class, [
                'label' => 'ekyna_commerce.country.label.singular',
                'class' => $this->countryClass,
            ])
            /*TODO->add('state', ResourceType::class, [
                'label' => 'ekyna_commerce.state.label.singular',
                'class' => $this->stateClass,
            ])*/
            ->add('postalCodeMatch', Type\TextType::class, [
                'label'    => 'ekyna_commerce.tax.field.postal_code_match',
                'required' => false,
            ]);
    }
}
