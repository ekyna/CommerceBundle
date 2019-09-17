<?php

namespace Ekyna\Bundle\CommerceBundle\Form;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\UnitChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Common\Model\Units;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type as SF;

/**
 * Class StockSubjectFormBuilder
 * @package Ekyna\Bundle\CommerceBundle\Form
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockSubjectFormBuilder
{
    /**
     * @var FormInterface|FormBuilderInterface
     */
    protected $form;


    /**
     * Initializes the builder.
     *
     * @param FormInterface|FormBuilderInterface $form
     */
    public function initialize($form)
    {
        if (!($form instanceof FormInterface || $form instanceof FormBuilderInterface)) {
            throw new UnexpectedTypeException($form, [FormInterface::class,FormBuilderInterface::class]);
        }

        $this->form = $form;
    }

    /**
     * Returns the form.
     *
     * @return FormInterface|FormBuilderInterface
     */
    protected function getForm()
    {
        return $this->form;
    }

    /**
     * Adds the "end of life" field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addEndOfLifeField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_commerce.stock_subject.field.end_of_life',
            'required' => false,
            'attr'     => [
                'align_with_widget' => true,
            ],
        ], $options);

        $this->form->add('endOfLife', SF\CheckboxType::class, $options);

        return $this;
    }

    /**
     * Adds the geocode field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addGeocodeField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_commerce.field.geocode',
            'required' => false,
        ], $options);

        $this->form->add('geocode', SF\TextType::class, $options);

        return $this;
    }

    /**
     * Adds the minimum order quantity field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addMinimumOrderQuantity(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_commerce.stock_subject.field.minimum_order_quantity',
            'scale'    => 3, // TODO Packaging format
            'required' => true,
        ], $options);

        $this->form->add('minimumOrderQuantity', SF\NumberType::class, $options);

        return $this;
    }

    /**
     * Adds the quote only field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addQuoteOnlyField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_commerce.stock_subject.field.quote_only',
            'required' => false,
            'attr'     => [
                'align_with_widget' => true,
            ],
        ], $options);

        $this->form->add('quoteOnly', SF\CheckboxType::class, $options);

        return $this;
    }

    /**
     * Adds the stock replenishment time field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addReplenishmentTime(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_commerce.stock_subject.field.replenishment_time',
            'required' => true,
        ], $options);

        $this->form->add('replenishmentTime', SF\IntegerType::class, $options);

        return $this;
    }

    /**
     * Adds the stock mode field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addStockMode(array $options = [])
    {
        $options = array_replace([
            'label'   => 'ekyna_commerce.stock_subject.field.mode',
            'choices' => StockSubjectModes::getChoices(),
            'select2' => false,
        ], $options);

        $this->form->add('stockMode', SF\ChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the stock floor field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addStockFloor(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_commerce.stock_subject.field.floor',
            'scale'    => 3, // TODO Packaging format
            'required' => false,
        ], $options);

        $this->form->add('stockFloor', SF\NumberType::class, $options);

        return $this;
    }

    /**
     * Adds the weight field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addWeightField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_core.field.weight',
            'scale'    => 3,
            'attr'     => [
                'placeholder' => 'ekyna_core.field.weight',
                'input_group' => ['append' => Units::getSymbol(Units::KILOGRAM)],
            ],
            'required' => !(isset($options['disabled']) && $options['disabled']),
        ], $options);

        $this->form->add('weight', SF\NumberType::class, $options);

        return $this;
    }

    /**
     * Adds the width field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addWidthField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_core.field.width',
            'attr'     => [
                'placeholder' => 'ekyna_core.field.width',
                'input_group' => ['append' => Units::getSymbol(Units::MILLIMETER)],
            ],
            'required' => true,
        ], $options);

        $this->form->add('width', SF\IntegerType::class, $options);

        return $this;
    }

    /**
     * Adds the height field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addHeightField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_core.field.height',
            'attr'     => [
                'placeholder' => 'ekyna_core.field.height',
                'input_group' => ['append' => Units::getSymbol(Units::MILLIMETER)],
            ],
            'required' => true,
        ], $options);

        $this->form->add('height', SF\IntegerType::class, $options);

        return $this;
    }

    /**
     * Adds the depth field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addDepthField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_core.field.depth',
            'attr'     => [
                'placeholder' => 'ekyna_core.field.depth',
                'input_group' => ['append' => Units::getSymbol(Units::MILLIMETER)],
            ],
            'required' => true,
        ], $options);

        $this->form->add('depth', SF\IntegerType::class, $options);

        return $this;
    }

    /**
     * Adds the quantity unit field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addUnitField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_commerce.unit.label',
            'attr'     => [
                'placeholder' => 'ekyna_commerce.unit.label',
            ],
            'required' => true,
            'select2' => false,
        ], $options);

        $this->form->add('unit', UnitChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the package weight field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addPackageWeightField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_commerce.stock_subject.field.package_weight',
            'scale'    => 3,
            'attr'     => [
                'placeholder' => 'ekyna_commerce.stock_subject.field.package_weight',
                'input_group' => ['append' => Units::getSymbol(Units::KILOGRAM)],
            ],
            'required' => true,
        ], $options);

        $this->form->add('packageWeight', SF\NumberType::class, $options);

        return $this;
    }

    /**
     * Adds the package width field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addPackageWidthField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_commerce.stock_subject.field.package_width',
            'attr'     => [
                'placeholder' => 'ekyna_commerce.stock_subject.field.package_width',
                'input_group' => ['append' => Units::getSymbol(Units::MILLIMETER)],
            ],
            'required' => true,
        ], $options);

        $this->form->add('packageWidth', SF\IntegerType::class, $options);

        return $this;
    }

    /**
     * Adds the package height field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addPackageHeightField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_commerce.stock_subject.field.package_height',
            'attr'     => [
                'placeholder' => 'ekyna_commerce.stock_subject.field.package_height',
                'input_group' => ['append' => Units::getSymbol(Units::MILLIMETER)],
            ],
            'required' => true,
        ], $options);

        $this->form->add('packageHeight', SF\IntegerType::class, $options);

        return $this;
    }

    /**
     * Adds the package depth field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addPackageDepthField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_commerce.stock_subject.field.package_depth',
            'attr'     => [
                'placeholder' => 'ekyna_commerce.stock_subject.field.package_depth',
                'input_group' => ['append' => Units::getSymbol(Units::MILLIMETER)],
            ],
            'required' => true,
        ], $options);

        $this->form->add('packageDepth', SF\IntegerType::class, $options);

        return $this;
    }
}
