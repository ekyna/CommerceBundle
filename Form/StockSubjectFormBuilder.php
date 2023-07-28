<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\UnitChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Component\Commerce\Common\Model\Units;
use Symfony\Component\Form\Extension\Core\Type as SF;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

use function array_replace;
use function Symfony\Component\Translation\t;

/**
 * Class StockSubjectFormBuilder
 * @package Ekyna\Bundle\CommerceBundle\Form
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockSubjectFormBuilder
{
    protected FormInterface|FormBuilderInterface $form;

    public function initialize(FormInterface|FormBuilderInterface $form): void
    {
        $this->form = $form;
    }

    protected function getForm(): FormInterface|FormBuilderInterface
    {
        return $this->form;
    }

    public function addEndOfLifeField(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('stock_subject.field.end_of_life', [], 'EkynaCommerce'),
            'required' => false,
            'attr'     => [
                'align_with_widget' => true,
            ],
        ], $options);

        $this->form->add('endOfLife', SF\CheckboxType::class, $options);

        return $this;
    }

    public function addGeocodeField(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('field.geocode', [], 'EkynaCommerce'),
            'required' => false,
        ], $options);

        $this->form->add('geocode', SF\TextType::class, $options);

        return $this;
    }

    public function addMinimumOrderQuantity(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('stock_subject.field.minimum_order_quantity', [], 'EkynaCommerce'),
            'decimal'  => true,
            'scale'    => 3, // TODO Packaging format
            'required' => true,
        ], $options);

        $this->form->add('minimumOrderQuantity', SF\NumberType::class, $options);

        return $this;
    }

    /**
     * Adds the released at field.
     */
    public function addReleasedAtField(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('stock_subject.field.released_at', [], 'EkynaCommerce'),
            'required' => false,
        ], $options);

        $this->form->add('releasedAt', SF\DateType::class, $options);

        return $this;
    }

    public function addQuoteOnlyField(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('stock_subject.field.quote_only', [], 'EkynaCommerce'),
            'required' => false,
            'attr'     => [
                'align_with_widget' => true,
            ],
        ], $options);

        $this->form->add('quoteOnly', SF\CheckboxType::class, $options);

        return $this;
    }

    public function addReplenishmentTime(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('stock_subject.field.replenishment_time', [], 'EkynaCommerce'),
            'required' => true,
        ], $options);

        $this->form->add('replenishmentTime', SF\IntegerType::class, $options);

        return $this;
    }

    public function addStockMode(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'   => t('stock_subject.field.mode', [], 'EkynaCommerce'),
            'class'   => StockSubjectModes::class,
            'select2' => false,
        ], $options);

        $this->form->add('stockMode', ConstantChoiceType::class, $options);

        return $this;
    }

    public function addStockFloor(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('stock_subject.field.floor', [], 'EkynaCommerce'),
            'decimal'  => true,
            'scale'    => 3, // TODO Packaging format
            'required' => false,
        ], $options);

        $this->form->add('stockFloor', SF\NumberType::class, $options);

        return $this;
    }

    public function addWeightField(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('field.weight', [], 'EkynaUi'),
            'decimal'  => true,
            'scale'    => 3,
            'attr'     => [
                'placeholder' => t('field.weight', [], 'EkynaUi'),
                'input_group' => ['append' => Units::getSymbol(Units::KILOGRAM)],
            ],
            'required' => !(isset($options['disabled']) && $options['disabled']),
        ], $options);

        $this->form->add('weight', SF\NumberType::class, $options);

        return $this;
    }

    public function addWidthField(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('field.width', [], 'EkynaUi'),
            'attr'     => [
                'placeholder' => t('field.width', [], 'EkynaUi'),
                'input_group' => ['append' => Units::getSymbol(Units::MILLIMETER)],
            ],
            'required' => true,
        ], $options);

        $this->form->add('width', SF\IntegerType::class, $options);

        return $this;
    }

    public function addHeightField(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('field.height', [], 'EkynaUi'),
            'attr'     => [
                'placeholder' => t('field.height', [], 'EkynaUi'),
                'input_group' => ['append' => Units::getSymbol(Units::MILLIMETER)],
            ],
            'required' => true,
        ], $options);

        $this->form->add('height', SF\IntegerType::class, $options);

        return $this;
    }

    public function addDepthField(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('field.depth', [], 'EkynaUi'),
            'attr'     => [
                'placeholder' => t('field.depth', [], 'EkynaUi'),
                'input_group' => ['append' => Units::getSymbol(Units::MILLIMETER)],
            ],
            'required' => true,
        ], $options);

        $this->form->add('depth', SF\IntegerType::class, $options);

        return $this;
    }

    public function addPhysicalField(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('field.physical', [], 'EkynaCommerce'),
            'required' => false,
            'attr'     => [
                'align_with_widget' => true,
            ],
        ], $options);

        $this->form->add('physical', SF\CheckboxType::class, $options);

        return $this;
    }

    public function addUnitField(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('unit.label', [], 'EkynaCommerce'),
            'attr'     => [
                'placeholder' => t('unit.label', [], 'EkynaCommerce'),
            ],
            'required' => true,
            'select2'  => false,
        ], $options);

        $this->form->add('unit', UnitChoiceType::class, $options);

        return $this;
    }

    public function addPackageWeightField(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('stock_subject.field.package_weight', [], 'EkynaCommerce'),
            'decimal'  => true,
            'scale'    => 3,
            'attr'     => [
                'placeholder' => t('stock_subject.field.package_weight', [], 'EkynaCommerce'),
                'input_group' => ['append' => Units::getSymbol(Units::KILOGRAM)],
            ],
            'required' => true,
        ], $options);

        $this->form->add('packageWeight', SF\NumberType::class, $options);

        return $this;
    }

    public function addPackageWidthField(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('stock_subject.field.package_width', [], 'EkynaCommerce'),
            'attr'     => [
                'placeholder' => t('stock_subject.field.package_width', [], 'EkynaCommerce'),
                'input_group' => ['append' => Units::getSymbol(Units::MILLIMETER)],
            ],
            'required' => true,
        ], $options);

        $this->form->add('packageWidth', SF\IntegerType::class, $options);

        return $this;
    }

    public function addPackageHeightField(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('stock_subject.field.package_height', [], 'EkynaCommerce'),
            'attr'     => [
                'placeholder' => t('stock_subject.field.package_height', [], 'EkynaCommerce'),
                'input_group' => ['append' => Units::getSymbol(Units::MILLIMETER)],
            ],
            'required' => true,
        ], $options);

        $this->form->add('packageHeight', SF\IntegerType::class, $options);

        return $this;
    }

    public function addPackageDepthField(array $options = []): StockSubjectFormBuilder
    {
        $options = array_replace([
            'label'    => t('stock_subject.field.package_depth', [], 'EkynaCommerce'),
            'attr'     => [
                'placeholder' => t('stock_subject.field.package_depth', [], 'EkynaCommerce'),
                'input_group' => ['append' => Units::getSymbol(Units::MILLIMETER)],
            ],
            'required' => true,
        ], $options);

        $this->form->add('packageDepth', SF\IntegerType::class, $options);

        return $this;
    }
}
