<?php

namespace Ekyna\Bundle\CommerceBundle\Form;

use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\PriceType;
use Ekyna\Component\Commerce\Common\Model as Common;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type as SF;
use Ekyna\Bundle\CommerceBundle\Form\Type as CO;

/**
 * Class SubjectFormBuilder
 * @package Ekyna\Bundle\CommerceBundle\Form
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectFormBuilder
{
    /**
     * @var FormInterface
     */
    protected $form;


    /**
     * Initializes the builder.
     *
     * @param FormInterface $form
     */
    public function initialize(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * Adds the customer groups field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addCustomerGroupsField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_commerce.customer_group.label.plural',
            'multiple' => true,
            'required' => false,
        ], $options);

        $this->form->add('customerGroups', CO\Customer\CustomerGroupChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the designation field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addDesignationField(array $options = [])
    {
        $options = array_replace([
            'label' => 'ekyna_core.field.designation',
        ], $options);

        $this->form->add('designation', SF\TextType::class, $options);

        return $this;
    }

    /**
     * Adds the net price field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addNetPriceField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_commerce.field.net_price',
            'required' => !(isset($options['disabled']) && $options['disabled']),
        ], $options);

        $this->form->add('netPrice', PriceType::class, $options);

        return $this;
    }

    /**
     * Adds the adjustments field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addAdjustmentsField(array $options = [])
    {
        $options = array_replace([
            'label'                 => 'ekyna_commerce.adjustment.label.plural',
            'add_button_text'       => 'ekyna_commerce.sale.form.add_item_adjustment',
            'delete_button_confirm' => 'ekyna_commerce.sale.form.remove_item_adjustment',
            'attr'                  => ['label_col' => 2, 'widget_col' => 10],
            'modes'                 => [Common\AdjustmentModes::MODE_FLAT],
            'types'                 => [Common\AdjustmentTypes::TYPE_INCLUDED],
            'required'              => false,
        ], $options);

        $this->form->add('adjustments', CO\Common\AdjustmentsType::class, $options);

        return $this;
    }

    /**
     * Adds the tax group field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addTaxGroupField(array $options = [])
    {
        /*$options = array_replace([
            'allow_new' => true,
        ], $options);*/

        $this->form->add('taxGroup', CO\Pricing\TaxGroupChoiceType::class, $options);

        return $this;
    }
}