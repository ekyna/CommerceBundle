<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\ResourceBundle\Form\DataTransformer\DecimalToStringTransformer;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class PriceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceType extends AbstractType
{
    private string $defaultCurrency;
    private string $defaultVatMode;

    public function __construct(string $defaultCurrency, string $defaultVatMode)
    {
        $this->defaultCurrency = $defaultCurrency;
        $this->defaultVatMode = $defaultVatMode;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(
            new DecimalToStringTransformer(Currencies::getFractionDigits($options['currency']))
        );

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
                'label'    => t('pricing.vat_display_mode.ati', [], 'EkynaCommerce'),
                'data'     => 'ati' === $this->defaultVatMode,
                'required' => false,
                'attr'     => [
                    'class' => 'commerce-price-mode',
                ],
            ])
            ->getForm();

        $builder->setAttribute('mode_prototype', $mode);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var FormInterface $input */
        $input = $form->getConfig()->getAttribute('input_prototype');
        $view->vars['input_prototype'] = $input->setParent($form)->createView($view);

        /** @var FormInterface $mode */
        $mode = $form->getConfig()->getAttribute('mode_prototype');
        $view->vars['mode_prototype'] = $mode->setParent($form)->createView($view);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'commerce-price-value');

        $view->vars['config'] = [
            'tax_group' => sprintf('[name="%s[%s]"]', $view->parent->vars['full_name'], $options['tax_group']),
            'precision' => Currencies::getFractionDigits($options['currency']),
            'rates'     => $options['rates'],
        ];

        $view->vars['currency'] = Currencies::getSymbol($options['currency']);
    }

    public function configureOptions(OptionsResolver $resolver): void
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

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_price';
    }

    public function getParent(): ?string
    {
        return Type\TextType::class;
    }
}
