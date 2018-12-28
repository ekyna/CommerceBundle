<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PriceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceType extends AbstractType
{
    /**
     * @var string
     */
    private $defaultCurrency;

    /**
     * @var string
     */
    private $defaultVatMode;


    /**
     * Constructor.
     *
     * @param string $defaultCurrency
     * @param string $defaultVatMode
     */
    public function __construct(string $defaultCurrency, string $defaultVatMode)
    {
        $this->defaultCurrency = $defaultCurrency;
        $this->defaultVatMode = $defaultVatMode;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $input = $builder
            ->create('input', Type\TextType::class, [
                'label' => false,
                'attr'  => [
                    'class' => 'commerce-price-input',
                ],
            ])
            ->getForm();

        $builder->setAttribute('input_prototype', $input);

        $mode = $builder
            ->create('mode', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.pricing.vat_display_mode.ati',
                'data'     => 'ati' === $this->defaultVatMode,
                'required' => false,
                'attr'     => [
                    'class' => 'commerce-price-mode',
                ],
            ])
            ->getForm();

        $builder->setAttribute('mode_prototype', $mode);
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var FormInterface $input */
        $input = $form->getConfig()->getAttribute('input_prototype');
        $view->vars['input_prototype'] = $input->setParent($form)->createView($view);

        /** @var FormInterface $mode */
        $mode = $form->getConfig()->getAttribute('mode_prototype');
        $view->vars['mode_prototype'] = $mode->setParent($form)->createView($view);
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'commerce-price-value');

        $view->vars['config'] = [
            'tax_group' => sprintf('[name="%s[%s]"]', $view->parent->vars['full_name'], $options['tax_group']),
            'precision' => Intl::getCurrencyBundle()->getFractionDigits($options['currency']),
            'rates'     => $options['rates'],
        ];

//        //$view->vars['group_selector'] = $options['tax_group_selector'];
//        $view->vars['group_selector'] = sprintf('[name="%s[%s]"]', $view->parent->vars['full_name'], $options['tax_group_selector']);
//        $view->vars['group_selector'] = '[name="' . $view->parent->vars['full_name'] . '[' . $options['tax_group_selector'] . ']"]';
        $view->vars['currency'] = Intl::getCurrencyBundle()->getCurrencySymbol($options['currency']);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'tax_group' => 'taxGroup',
                'currency'  => $this->defaultCurrency,
                'rates'     => [],
            ])
            ->setAllowedTypes('tax_group', 'string')
            ->setAllowedTypes('currency', 'string')
            ->setAllowedTypes('rates', 'array');
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_price';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return Type\TextType::class;
    }
}
