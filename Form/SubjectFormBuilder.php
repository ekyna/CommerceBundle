<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form;

use Ekyna\Bundle\CommerceBundle\Form\Type as CO;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\PriceType;
use Ekyna\Component\Commerce\Common\Model as Common;
use Symfony\Component\Form\Extension\Core\Type as SF;
use Symfony\Component\Form\FormInterface;

use function Symfony\Component\Translation\t;

/**
 * Class SubjectFormBuilder
 * @package Ekyna\Bundle\CommerceBundle\Form
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectFormBuilder
{
    protected FormInterface $form;

    public function initialize(FormInterface $form)
    {
        $this->form = $form;
    }

    public function addCustomerGroupsField(array $options = []): SubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('customer_group.label.plural', [], 'EkynaCommerce'),
            'multiple' => true,
            'required' => false,
        ], $options);

        $this->form->add('customerGroups', CO\Customer\CustomerGroupChoiceType::class, $options);

        return $this;
    }

    public function addDesignationField(array $options = []): SubjectFormBuilder
    {
        $options = array_replace([
            'label' => t('field.designation', [], 'EkynaUi'),
        ], $options);

        $this->form->add('designation', SF\TextType::class, $options);

        return $this;
    }

    public function addNetPriceField(array $options = []): SubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('field.net_price', [], 'EkynaCommerce'),
            'required' => !(isset($options['disabled']) && $options['disabled']),
        ], $options);

        $this->form->add('netPrice', PriceType::class, $options);

        return $this;
    }

    public function addAdjustmentsField(array $options = []): SubjectFormBuilder
    {
        $options = array_replace([
            'label'                 => t('adjustment.label.plural', [], 'EkynaCommerce'),
            'add_button_text'       => t('sale.form.add_item_adjustment', [], 'EkynaCommerce'),
            'delete_button_confirm' => t('sale.form.remove_item_adjustment', [], 'EkynaCommerce'),
            'attr'                  => ['label_col' => 2, 'widget_col' => 10],
            'modes'                 => [Common\AdjustmentModes::MODE_FLAT],
            'types'                 => [Common\AdjustmentTypes::TYPE_INCLUDED],
            'required'              => false,
        ], $options);

        $this->form->add('adjustments', CO\Common\AdjustmentsType::class, $options);

        return $this;
    }

    public function addTaxGroupField(array $options = []): SubjectFormBuilder
    {
        /*$options = array_replace([
            'allow_new' => true,
        ], $options);*/

        $this->form->add('taxGroup', CO\Pricing\TaxGroupChoiceType::class, $options);

        return $this;
    }
}
