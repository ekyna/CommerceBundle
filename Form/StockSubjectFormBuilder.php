<?php

namespace Ekyna\Bundle\CommerceBundle\Form;

use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes;
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
}
